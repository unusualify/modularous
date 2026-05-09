<?php

namespace Modules\Cms\Jobs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\Cms\Entities\Page;
use Modules\Cms\Events\CmsPublishWindowBoundaryReached;

/**
 * Scans configured Eloquent models for recent {@code publish_start_date} / {@code publish_end_date} boundary crossings
 * and dispatches {@see CmsPublishWindowBoundaryReached}.
 */
final class ScanCmsPublishWindowBoundariesJob
{
    use Dispatchable;

    public function handle(): void
    {
        if (! modularousConfig('cms_features.enabled', true)) {
            return;
        }

        if (! modularousConfig('cms_schedule.enabled', true)) {
            return;
        }

        $window = max(1, (int) modularousConfig('cms_schedule.boundary_window_minutes', 6));
        $since = now()->subMinutes($window);
        $until = now();

        $fired = false;

        foreach ($this->resolvePublishWindowModelClasses() as $modelClass) {
            $instance = new $modelClass;
            $table = $instance->getTable();
            if (! Schema::hasTable($table)) {
                continue;
            }

            $keyName = $instance->getKeyName();

            if (Schema::hasColumn($table, 'publish_start_date')) {
                $startIds = $modelClass::query()
                    ->whereNotNull('publish_start_date')
                    ->whereBetween('publish_start_date', [$since, $until])
                    ->pluck($keyName)
                    ->all();

                foreach ($startIds as $id) {
                    event(new CmsPublishWindowBoundaryReached($modelClass, $id, 'publish_start'));
                    $fired = true;
                    $this->maybeLog($modelClass, $id, 'publish_start');
                }
            }

            if (Schema::hasColumn($table, 'publish_end_date')) {
                $endIds = $modelClass::query()
                    ->whereNotNull('publish_end_date')
                    ->whereBetween('publish_end_date', [$since, $until])
                    ->pluck($keyName)
                    ->all();

                foreach ($endIds as $id) {
                    event(new CmsPublishWindowBoundaryReached($modelClass, $id, 'publish_end'));
                    $fired = true;
                    $this->maybeLog($modelClass, $id, 'publish_end');
                }
            }
        }

        if ($fired) {
            $this->maybeFlushCacheTags();
        }
    }

    /**
     * @return list<class-string<Model>>
     */
    private function resolvePublishWindowModelClasses(): array
    {
        $configured = modularousConfig('cms_schedule.publish_window_models', null);

        if ($configured === null) {
            return [Page::class];
        }

        if (! is_array($configured)) {
            return [];
        }

        $out = [];
        foreach ($configured as $class) {
            if (! is_string($class) || $class === '' || ! class_exists($class)) {
                continue;
            }
            if (! is_subclass_of($class, Model::class, true)) {
                continue;
            }
            $out[] = $class;
        }

        return array_values(array_unique($out));
    }

    private function maybeLog(string $modelClass, int|string $modelId, string $boundary): void
    {
        if (! modularousConfig('cms_schedule.log_events', false)) {
            return;
        }

        Log::info('CMS publish window boundary', [
            'model' => $modelClass,
            'model_id' => $modelId,
            'boundary' => $boundary,
        ]);
    }

    private function maybeFlushCacheTags(): void
    {
        $tags = modularousConfig('cms_schedule.cache_flush_tags');
        if (! is_array($tags) || $tags === []) {
            return;
        }

        $store = Cache::getStore();
        if (! method_exists($store, 'tags')) {
            return;
        }

        try {
            Cache::tags($tags)->flush();
        } catch (\Throwable) {
            // Taggable store not available or driver unsupported
        }
    }
}
