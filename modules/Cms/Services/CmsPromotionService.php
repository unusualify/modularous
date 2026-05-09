<?php

namespace Modules\Cms\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\Cms\Contracts\CmsPromotionScopeApplierInterface;
use Modules\Cms\Events\CmsPromotionExecuted;
use Unusualify\Modularous\Entities\Enums\RevisionStatus;
use Unusualify\Modularous\Services\Security\SecurityService;

class CmsPromotionService
{
    public function __construct(
        protected SecurityService $securityService,
        protected CmsPromotionScopeApplierInterface $scopeApplier,
    ) {}

    public function plan(array $input = []): array
    {
        $scope = $this->resolveScope((array) ($input['scope'] ?? []));

        return [
            'dry_run' => (bool) ($input['dry_run'] ?? true),
            'scope' => $scope,
            'checkpoint' => modularousConfig('cms_promotion.approval.checkpoint_label', 'approval-checkpoint'),
            'steps' => [
                'validate',
                'dry_run_diff',
                'approval_check',
                'apply',
                'cache_flush',
                'audit_log',
            ],
        ];
    }

    public function promote(array $input = [], ?Authenticatable $user = null): array
    {
        $user = $user ?? $this->resolveUserFromPayload($input);

        $scope = $this->resolveScope((array) ($input['scope'] ?? []));
        $dryRun = (bool) ($input['dry_run'] ?? modularousConfig('cms_promotion.dry_run_required', true));

        if (modularousConfig('cms_promotion.approval.enabled', true) && ! $this->securityService->canPromote($user)) {
            return [
                'ok' => false,
                'stage' => 'approval_check',
                'message' => 'User is not allowed to approve or execute promotion.',
            ];
        }

        $report = [
            'ok' => true,
            'dry_run' => $dryRun,
            'scope' => $scope,
            'diff' => $this->dryRunDiff($scope),
            'cache_flushed' => false,
        ];

        if (! $dryRun) {
            $report['cache_flushed'] = $this->flushModularousCache();
            if (modularousConfig('cms_promotion.execute.flush_laravel_cache', false)) {
                Cache::flush();
                $report['laravel_cache_flushed'] = true;
            }

            $report['scope_effects'] = $this->scopeApplier->applyAfterPromotion($scope, [
                'dry_run' => false,
                'user' => $user,
                'diff' => $report['diff'],
                'report' => $report,
            ]);

            Event::dispatch(new CmsPromotionExecuted($scope, $report, $user));

            $this->recordPromotionAudit($user, $scope, $report);
        }

        Log::channel($this->auditLogChannel())->info('CMS promotion executed', [
            'dry_run' => $dryRun,
            'scope' => $scope,
            'executed_by' => $user?->getAuthIdentifier(),
            'executed_by_email' => $user?->email ?? null,
        ]);

        return $report;
    }

    protected function auditLogChannel(): string
    {
        return (string) modularousConfig('cms_promotion.audit.log_channel', 'modularous');
    }

    /**
     * Structured log + optional Spatie activity entry when the helper exists.
     */
    protected function recordPromotionAudit(?Authenticatable $user, array $scope, array $report): void
    {
        if (! modularousConfig('cms_promotion.audit.activity_log', true)) {
            return;
        }

        if (! function_exists('activity')) {
            return;
        }

        try {
            $act = activity()
                ->withProperties([
                    'scope' => $scope,
                    'cache_flushed' => $report['cache_flushed'] ?? false,
                    'diff_meta' => $report['diff']['meta'] ?? [],
                ])
                ->event('cms_promotion');

            if ($user instanceof Model) {
                $act->causedBy($user);
            }

            $act->log('cms_promotion_execute');
        } catch (\Throwable) {
            // Activity table / package optional in some installs
        }
    }

    /**
     * Resolve an authenticated user when promotion runs from a queued job (no HTTP guard).
     *
     * @param array<string, mixed> $input
     */
    protected function resolveUserFromPayload(array $input): ?Authenticatable
    {
        $id = $input['user_id'] ?? null;
        if ($id === null || $id === '') {
            return null;
        }

        $model = config('auth.providers.users.model');
        if (! is_string($model) || ! class_exists($model)) {
            return null;
        }

        return $model::query()->find($id);
    }

    /**
     * @param array<string, mixed> $scope
     */
    protected function dryRunDiff(array $scope): array
    {
        $primary = $this->buildScopeSnapshots($scope, null);

        $meta = [
            'generated_at' => now()->toIso8601String(),
            'environment' => app()->environment(),
            'source_connection' => (string) config('database.default'),
            'note' => 'Primary snapshot uses the default application DB connection. When `cms_promotion.compare.connection` is set, a second snapshot and numeric deltas are included.',
        ];

        $out = array_merge(['meta' => $meta], $primary);

        $compare = (string) modularousConfig('cms_promotion.compare.connection', '');
        if ($compare !== '') {
            try {
                $this->assertCompareConnectionAllowed($compare);
                $secondary = $this->buildScopeSnapshots($scope, $compare);
                $out['comparison'] = [
                    'enabled' => true,
                    'target_connection' => $compare,
                    'target_label' => (string) modularousConfig('cms_promotion.compare.label', $compare),
                    'count_delta' => $this->diffIntegerLeaves($primary, $secondary),
                ];
                if (modularousConfig('cms_promotion.compare.include_full_target_snapshot', false)) {
                    $out['comparison']['target_snapshots'] = $secondary;
                }
            } catch (\Throwable $e) {
                $out['comparison'] = [
                    'enabled' => true,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $out;
    }

    /**
     * @param array<string, mixed> $scope
     * @return array<string, mixed>
     */
    protected function buildScopeSnapshots(array $scope, ?string $connectionName): array
    {
        return [
            'settings_changes' => $this->scopeIncludes($scope, 'settings')
                ? $this->summarizeSettings($connectionName)
                : [],
            'content_changes' => $this->scopeIncludes($scope, 'content')
                ? $this->summarizeContent($connectionName)
                : [],
            'seo_changes' => $this->scopeIncludes($scope, 'seo')
                ? $this->summarizeSeo($connectionName)
                : [],
            'redirect_changes' => $this->scopeIncludes($scope, 'redirects')
                ? $this->summarizeRedirects($connectionName)
                : [],
            'layout_changes' => $this->scopeIncludes($scope, 'layouts')
                ? $this->summarizeLayouts($connectionName)
                : [],
        ];
    }

    /**
     * Recursively subtract matching integer values (same array shape) for quick cross-DB drift.
     *
     * @return array<string, int>
     */
    protected function diffIntegerLeaves(array $a, array $b): array
    {
        $out = [];
        $this->walkIntegerDiff($a, $b, '', $out);

        return $out;
    }

    /**
     * @param array<string, int> $out
     */
    protected function walkIntegerDiff(mixed $a, mixed $b, string $path, array &$out): void
    {
        if (is_int($a) && is_int($b) && $a !== $b) {
            $out[$path === '' ? 'value' : $path] = $a - $b;

            return;
        }

        if (! is_array($a) || ! is_array($b)) {
            return;
        }

        foreach ($a as $key => $va) {
            $childPath = $path === '' ? (string) $key : $path . '.' . $key;
            if (! array_key_exists($key, $b)) {
                continue;
            }
            $this->walkIntegerDiff($va, $b[$key], $childPath, $out);
        }
    }

    protected function assertCompareConnectionAllowed(string $name): void
    {
        $allowed = (array) modularousConfig('cms_promotion.compare.allowed_connections', []);
        if ($allowed === []) {
            return;
        }

        if (! in_array($name, $allowed, true)) {
            throw new \InvalidArgumentException("Promotion compare connection [{$name}] is not in cms_promotion.compare.allowed_connections.");
        }
    }

    /**
     * @param array<string, mixed> $scope
     */
    protected function scopeIncludes(array $scope, string $key): bool
    {
        return (bool) ($scope[$key] ?? false);
    }

    protected function cmsTable(string $key, string $default): string
    {
        return (string) modularousConfig('tables.' . $key, $default);
    }

    protected function db(?string $connectionName): Connection
    {
        if ($connectionName === null || $connectionName === '') {
            return DB::connection();
        }

        return DB::connection($connectionName);
    }

    protected function schemaHasTable(?string $connectionName, string $table): bool
    {
        if ($connectionName === null || $connectionName === '') {
            return Schema::hasTable($table);
        }

        return Schema::connection($connectionName)->hasTable($table);
    }

    protected function summarizeSettings(?string $connectionName = null): array
    {
        $table = $this->cmsTable('cms_site_settings', 'um_cms_site_settings');
        if (! $this->schemaHasTable($connectionName, $table)) {
            return $this->tableMissingPayload($table);
        }

        $q = $this->db($connectionName)->table($table);

        return [
            'available' => true,
            'table' => $table,
            'connection' => $connectionName ?? (string) config('database.default'),
            'total_rows' => (int) $q->count(),
            'active_rows' => (int) $this->db($connectionName)->table($table)->where('is_active', true)->count(),
            'rows_by_group' => $this->db($connectionName)->table($table)
                ->select('group_key', DB::raw('count(*) as c'))
                ->groupBy('group_key')
                ->orderByDesc('c')
                ->limit(25)
                ->get()
                ->pluck('c', 'group_key')
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function summarizeContent(?string $connectionName = null): array
    {
        $pageTable = $this->cmsTable('cms_pages', 'um_cms_pages');
        $revisionTable = $this->cmsTable('cms_pages_revisions', 'um_cms_pages_revisions');
        $routeTable = $this->cmsTable('cms_url_routes', 'um_cms_url_routes');

        $out = [
            'available' => true,
            'pages' => [],
            'page_revisions' => [],
            'url_routes' => [],
        ];

        if ($this->schemaHasTable($connectionName, $pageTable)) {
            $out['pages'] = [
                'table' => $pageTable,
                'connection' => $connectionName ?? (string) config('database.default'),
                'total_rows' => (int) $this->db($connectionName)->table($pageTable)->count(),
                'published_rows' => (int) $this->db($connectionName)->table($pageTable)->where('published', true)->count(),
            ];
        } else {
            $out['pages'] = $this->tableMissingPayload($pageTable);
        }

        if ($this->schemaHasTable($connectionName, $revisionTable)) {
            $out['page_revisions'] = [
                'table' => $revisionTable,
                'pending_rows' => (int) $this->db($connectionName)->table($revisionTable)
                    ->where('status', RevisionStatus::Pending->value)
                    ->count(),
            ];
        } else {
            $out['page_revisions'] = $this->tableMissingPayload($revisionTable);
        }

        if ($this->schemaHasTable($connectionName, $routeTable)) {
            $out['url_routes'] = [
                'table' => $routeTable,
                'total_rows' => (int) $this->db($connectionName)->table($routeTable)->count(),
                'rows_by_kind' => $this->db($connectionName)->table($routeTable)
                    ->select('kind', DB::raw('count(*) as c'))
                    ->groupBy('kind')
                    ->orderByDesc('c')
                    ->get()
                    ->pluck('c', 'kind')
                    ->all(),
            ];
        } else {
            $out['url_routes'] = $this->tableMissingPayload($routeTable);
        }

        return $out;
    }

    /**
     * @return array<string, mixed>
     */
    protected function summarizeSeo(?string $connectionName = null): array
    {
        $table = $this->cmsTable('cms_page_translations', 'um_cms_page_translations');
        if (! $this->schemaHasTable($connectionName, $table)) {
            return $this->tableMissingPayload($table);
        }

        $withMeta = (int) $this->db($connectionName)->table($table)->where(function ($q): void {
            $q->where(function ($q2): void {
                $q2->whereNotNull('seo_title')->where('seo_title', '!=', '');
            })->orWhere(function ($q3): void {
                $q3->whereNotNull('seo_description')->where('seo_description', '!=', '');
            });
        })->count();

        $total = (int) $this->db($connectionName)->table($table)->count();

        return [
            'available' => true,
            'table' => $table,
            'connection' => $connectionName ?? (string) config('database.default'),
            'translation_rows_with_seo_fields' => $withMeta,
            'translation_rows_total' => $total,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function summarizeRedirects(?string $connectionName = null): array
    {
        $table = $this->cmsTable('cms_redirects', 'um_cms_redirects');
        if (! $this->schemaHasTable($connectionName, $table)) {
            return $this->tableMissingPayload($table);
        }

        return [
            'available' => true,
            'table' => $table,
            'connection' => $connectionName ?? (string) config('database.default'),
            'total_rows' => (int) $this->db($connectionName)->table($table)->count(),
            'active_rows' => (int) $this->db($connectionName)->table($table)->where('is_active', true)->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function summarizeLayouts(?string $connectionName = null): array
    {
        $table = $this->cmsTable('cms_pages', 'um_cms_pages');
        if (! $this->schemaHasTable($connectionName, $table)) {
            return $this->tableMissingPayload($table);
        }

        $rows = $this->db($connectionName)->table($table)
            ->whereNotNull('layout')
            ->where('layout', '!=', '')
            ->select('layout', DB::raw('count(*) as c'))
            ->groupBy('layout')
            ->orderByDesc('c')
            ->limit(40)
            ->get()
            ->pluck('c', 'layout')
            ->all();

        return [
            'available' => true,
            'table' => $table,
            'connection' => $connectionName ?? (string) config('database.default'),
            'distinct_layout_keys' => count($rows),
            'rows_by_layout' => $rows,
        ];
    }

    /**
     * @return array{available: false, table: string}
     */
    protected function tableMissingPayload(string $table): array
    {
        return [
            'available' => false,
            'table' => $table,
        ];
    }

    protected function resolveScope(array $requestedScope): array
    {
        $defaults = (array) modularousConfig('cms_promotion.scope', []);

        if ($requestedScope === []) {
            return $defaults;
        }

        return array_merge($defaults, $requestedScope);
    }

    protected function flushModularousCache(): bool
    {
        if (app()->bound('modularous.cache')) {
            app('modularous.cache')->flush();

            return true;
        }

        return false;
    }
}
