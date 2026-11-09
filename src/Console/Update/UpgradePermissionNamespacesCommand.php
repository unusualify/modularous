<?php

namespace Unusualify\Modularous\Console\Update;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Unusualify\Modularous\Console\BaseCommand;
use Unusualify\Modularous\Entities\Enums\Permission;
use Unusualify\Modularous\Facades\Modularous;

/**
 * Idempotent `{route}_{type}` → `{module}::{route}_{type}` permission rename.
 *
 * Names are renamed in place, so role and user assignments keep pointing at the same rows.
 * A route defined by several modules is renamed to its --owner module and copied, with all
 * of its assignments, to every other module that defines it.
 */
class UpgradePermissionNamespacesCommand extends BaseCommand
{
    /**
     * @var string
     */
    protected $signature = 'modularous:upgrade:permission-namespaces
        {--owner=* : Owner module of a route shared by several modules, e.g. --owner=user=SystemUser}
        {--dry-run : Report the planned changes without writing}';

    /**
     * @var string
     */
    protected $description = 'Prefix permission names with their module ({route}_{type} → {module}::{route}_{type})';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $routeModules = $this->routeModuleMap();

        try {
            $owners = $this->parseOwners($routeModules);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $permissions = DB::table($this->permissionsTable())->orderBy('id')->get(['id', 'name', 'guard_name']);
        $plan = [];
        $skipped = [];
        $missingOwners = [];

        foreach ($permissions as $permission) {
            if (str_contains($permission->name, Permission::MODULE_SEPARATOR)) {
                continue;
            }

            $separatorPosition = mb_strrpos($permission->name, '_');

            if ($separatorPosition === false) {
                $skipped[$permission->name] = 'global';

                continue;
            }

            $prefix = mb_substr($permission->name, 0, $separatorPosition);
            $type = mb_substr($permission->name, $separatorPosition + 1);
            $modules = $routeModules[$prefix] ?? [];

            if ($modules === []) {
                $skipped[$permission->name] = 'no module route';

                continue;
            }

            $owner = count($modules) === 1 ? $modules[0] : ($owners[$prefix] ?? null);

            if ($owner === null) {
                $missingOwners[$prefix] = $modules;

                continue;
            }

            $plan[] = [
                'permission' => $permission,
                'target' => $this->namespacedName($owner, $prefix, $type),
                'mirrors' => array_map(
                    fn (string $module) => $this->namespacedName($module, $prefix, $type),
                    array_values(array_diff($modules, [$owner]))
                ),
            ];
        }

        if ($missingOwners !== []) {
            foreach ($missingOwners as $prefix => $modules) {
                $this->error("Route '{$prefix}' is defined by " . implode(', ', $modules) . "; pass --owner={$prefix}=<Module>.");
            }

            return self::FAILURE;
        }

        $this->report($plan, $skipped, $dryRun);

        if ($dryRun || $plan === []) {
            return self::SUCCESS;
        }

        DB::transaction(function () use ($plan) {
            foreach ($plan as $item) {
                $this->apply($item['permission'], $item['target'], $item['mirrors']);
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->info(sprintf('Namespaced %d permission(s).', count($plan)));

        return self::SUCCESS;
    }

    /**
     * Kebab route name → names of the enabled modules defining it.
     *
     * @return array<string, list<string>>
     */
    protected function routeModuleMap(): array
    {
        $map = [];

        foreach (Modularous::allEnabled() as $module) {
            foreach ($module->getRouteNames() ?? [] as $routeName) {
                $map[$this->routePrefix($routeName)][] = $module->getName();
            }
        }

        return array_map(fn (array $modules) => array_values(array_unique($modules)), $map);
    }

    /**
     * @param array<string, list<string>> $routeModules
     * @return array<string, string>
     */
    private function parseOwners(array $routeModules): array
    {
        $owners = [];

        foreach ((array) $this->option('owner') as $option) {
            [$route, $module] = array_pad(explode('=', (string) $option, 2), 2, null);

            if (! $route || ! $module) {
                throw new \InvalidArgumentException("Invalid --owner value '{$option}', expected <route>=<Module>.");
            }

            $prefix = $this->routePrefix($route);

            if (! in_array($module, $routeModules[$prefix] ?? [], true)) {
                throw new \InvalidArgumentException("Module '{$module}' does not define the '{$prefix}' route.");
            }

            $owners[$prefix] = $module;
        }

        return $owners;
    }

    /**
     * @param list<string> $mirrors
     */
    private function apply(object $permission, string $target, array $mirrors): void
    {
        foreach ($mirrors as $mirror) {
            $mirrorId = $this->findOrCreate($mirror, $permission->guard_name);
            $this->copyAssignments($permission->id, $mirrorId);
        }

        $existingId = $this->findId($target, $permission->guard_name);

        if ($existingId === null) {
            DB::table($this->permissionsTable())
                ->where('id', $permission->id)
                ->update(['name' => $target, 'updated_at' => now()]);

            return;
        }

        $this->copyAssignments($permission->id, $existingId);
        $this->deletePermission($permission->id);
    }

    private function copyAssignments(int $fromId, int $toId): void
    {
        $key = $this->permissionPivotKey();

        foreach ([$this->roleHasPermissionsTable(), $this->modelHasPermissionsTable()] as $table) {
            $rows = DB::table($table)
                ->where($key, $fromId)
                ->get()
                ->map(fn ($row) => array_merge((array) $row, [$key => $toId]))
                ->all();

            if ($rows !== []) {
                DB::table($table)->insertOrIgnore($rows);
            }
        }
    }

    private function deletePermission(int $id): void
    {
        $key = $this->permissionPivotKey();

        DB::table($this->roleHasPermissionsTable())->where($key, $id)->delete();
        DB::table($this->modelHasPermissionsTable())->where($key, $id)->delete();
        DB::table($this->permissionsTable())->where('id', $id)->delete();
    }

    private function findOrCreate(string $name, string $guardName): int
    {
        return $this->findId($name, $guardName) ?? (int) DB::table($this->permissionsTable())->insertGetId([
            'name' => $name,
            'guard_name' => $guardName,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function findId(string $name, string $guardName): ?int
    {
        $id = DB::table($this->permissionsTable())
            ->where('name', $name)
            ->where('guard_name', $guardName)
            ->value('id');

        return $id === null ? null : (int) $id;
    }

    /**
     * @param array<string, string> $skipped
     */
    private function report(array $plan, array $skipped, bool $dryRun): void
    {
        $rows = [];

        foreach ($plan as $item) {
            $rows[] = [$item['permission']->name, $item['target'], implode(', ', $item['mirrors'])];
        }

        if ($rows !== []) {
            $this->table(['Permission', 'Renamed to', 'Mirrored to'], $rows);
        }

        if ($skipped !== []) {
            $this->warn('Skipped: ' . collect($skipped)->map(fn ($reason, $name) => "{$name} ({$reason})")->implode(', '));
        }

        $this->info(sprintf(
            '%d to namespace, %d skipped%s',
            count($plan),
            count($skipped),
            $dryRun ? ' (dry-run)' : ''
        ));
    }

    private function namespacedName(string $moduleName, string $prefix, string $type): string
    {
        return Permission::permissionPrefix($moduleName, $prefix) . '_' . $type;
    }

    private function routePrefix(string $routeName): string
    {
        return Str::kebab(Str::studly($routeName));
    }

    private function permissionsTable(): string
    {
        return config('permission.table_names.permissions', 'permissions');
    }

    private function roleHasPermissionsTable(): string
    {
        return config('permission.table_names.role_has_permissions', 'role_has_permissions');
    }

    private function modelHasPermissionsTable(): string
    {
        return config('permission.table_names.model_has_permissions', 'model_has_permissions');
    }

    private function permissionPivotKey(): string
    {
        return config('permission.column_names.permission_pivot_key') ?? 'permission_id';
    }
}
