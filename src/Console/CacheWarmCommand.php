<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Support\Facades\DB;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Facades\ModularityCache;
use Unusualify\Modularity\Module;

class CacheWarmCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularity:cache:warm
                            {module? : The module name to warm cache for}
                            {routeName? : The route name to warm cache for}
                            {--logChannel= : Log the cache warming process}
                            {--counts : Warm only count caches}
                            {--items : Warm only item caches}
                            {--formItems : Warm only form item caches}
                            {--formattedItems : Warm only formatted item caches}
                            {--eager= : eager load items}
                            {--limit= : Limit the number of items to warm up}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm modularity caches for all or specific modules';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected function getLogChannel(): string
    {
        return $this->option('logChannel') ?? '';
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $moduleName = $this->argument('module');
        $routeName = $this->argument('routeName');

        // Check if caching is enabled
        if (! ModularityCache::isEnabled()) {
            $this->warn('MODULARITY: Caching is disabled.');

            return 1;
        }

        if ($moduleName) {
            $module = Modularity::find($moduleName);
            if (! $module) {
                $this->error("Module '{$moduleName}' not found.");

                return 1;
            }

            if (! ModularityCache::isEnabled($module->getName())) {
                $this->warn("{$module->getName()}: Caching is disabled");

                return 1;
            }

            if ($routeName) {
                $this->warmModuleCache($module, $routeName);
            } else {
                collect($module->getRouteNames())->each(function ($routeName) use ($module) {
                    if (! ModularityCache::isEnabled($module->getName(), $routeName)) {
                        $this->line("  <fg=yellow>⚠</> {$module->getName()} -> {$routeName}: Caching is disabled", verbosity: 'v');

                        return;
                    }
                    if (ModularityCache::isEnabled($module->getName(), $routeName, 'counts')) {
                        $this->warmModuleCounts($module, $routeName);
                    } else {
                        $this->line("  <fg=yellow>⚠</> {$module->getName()} -> {$routeName}: Counts cache is disabled", verbosity: 'v');
                    }

                    $this->warmModuleItems($module, $routeName);
                });
            }
        } else {
            $this->warmAllModulesCache();
        }

        return 0;
    }

    protected function getType(): string
    {
        $type = 'all';
        if ($this->option('counts')) {
            $type = 'counts';
        } elseif ($this->option('items')) {
        } elseif ($this->option('formItems')) {
            $type = 'formItems';
        } elseif ($this->option('formattedItems')) {
            $type = 'formattedItems';
        }

        return $type;
    }

    /**
     * Warm cache for a specific module.
     */
    protected function warmModuleCache(string $moduleName, string $routeName): void
    {
        // Try to find the module
        $module = Modularity::find($moduleName);

        if (! $module) {
            $this->error("Module '{$moduleName}' not found.");

            return;
        }

        if (! ModularityCache::isEnabled($module->getName())) {
            $this->warn("{$moduleName}: Caching is disabled");

            return;
        }

        if (! ModularityCache::isEnabled($module->getName(), $routeName)) {
            $this->line("  <fg=yellow>⚠</> {$moduleName} -> {$routeName}: Caching is disabled");

            return;
        }

        $this->info("{$moduleName} -> {$routeName}: Warming cache");

        $type = $this->getType();

        switch ($type) {
            case 'counts':
                $this->warmModuleCounts($module, $routeName);

                break;
            case 'items':
            case 'formItems':
            case 'formattedItems':
                $this->warmModuleItems($module, $routeName);

                break;
            default:
                $this->warmModuleCounts($module, $routeName);
                $this->warmModuleItems($module, $routeName);

                // $this->warmModuleAll($module, $routeName);
                break;
        }

        $this->newLine();
        $this->info("{$moduleName} -> {$routeName}: Cache warming completed");
    }

    /**
     * Warm count caches for a module.
     */
    protected function warmModuleCounts(Module $module, string $routeName): void
    {
        // Try to find and instantiate the repository for this module
        $controller = $module->getController($routeName);

        // if (! $controller || ! class_exists($controller)) {
        //     $this->line("  <fg=yellow>⚠</> No controller found for {$module}");

        //     return;
        // }

        try {
            $controller->setupDefaultFilters();
            $useUserAwareCache = $controller->getRepository()->shouldUseUserAwareCache();

            if ($useUserAwareCache) {
                $this->line("  <fg=yellow>⚠</> {$module->getName()} -> {$routeName}: Repository uses user aware caching, which is not supported in warming counts", verbosity: 'vv');

                return;
            }
            $countsList = $controller->getMainCountsList();

            foreach ($countsList as $filter) {
                try {
                    $controller->handleFilterCount($filter);
                    $this->line("  <fg=green>✓</> {$module->getName()} -> {$routeName}: Warmed '{$filter['slug']}' count cache", verbosity: 'vv');
                } catch (\Exception $e) {
                    $this->line("  <fg=red>✗</> {$module->getName()} -> {$routeName}: Failed to warm '{$filter['slug']}' count: " . $e->getMessage(), verbosity: 'vv');
                }
            }

        } catch (\Exception $e) {
            $this->error("{$module->getName()} -> {$routeName}: Failed to warm caches: " . $e->getMessage(), verbosity: 'vv');
            $this->error($e->getTraceAsString(), verbosity: 'vvv');
            if (($logChannel = $this->getLogChannel())) {
                // if log channel is exists, log the error
                \Illuminate\Support\Facades\Log::channel($logChannel)->error('Cache warm COUNTS error: ' . $e->getMessage(), [
                    'module' => $module->getName(),
                    'routeName' => $routeName,
                    'exception' => $e->getTraceAsString(),
                ]);
            }
        }
    }

    /**
     * Warm index caches for a module.
     */
    protected function warmModuleItems(Module $module, string $routeName): void
    {
        try {
            $type = $this->getType();
            // Try to find and instantiate the repository for this module
            $cacheFormItem = ModularityCache::isEnabled($module->getName(), $routeName, 'formItem');
            $cacheFormattedItem = ModularityCache::isEnabled($module->getName(), $routeName, 'formattedItem');

            if ($cacheFormItem) {
                $this->line("  <fg=green>✓</> {$module->getName()} -> {$routeName}: Warming form item cache", verbosity: 'vv');
            } else {
                $this->line("  <fg=yellow>⚠</> {$module->getName()} -> {$routeName}: Form item cache is disabled, skipping form item cache warming", verbosity: 'vv');
            }

            if ($cacheFormattedItem) {
                $this->line("  <fg=green>✓</> {$module->getName()} -> {$routeName}: Warming formatted item cache", verbosity: 'vv');
            } else {
                $this->line("  <fg=yellow>⚠</> {$module->getName()} -> {$routeName}: Formatted item cache is disabled, skipping formatted item cache warming", verbosity: 'vv');
            }

            if ($type === 'formItems') {
                $cacheFormattedItem = false;
            } elseif ($type === 'formattedItems') {
                $cacheFormItem = false;
            }

            if (! $cacheFormItem && ! $cacheFormattedItem) {
                return;
            }

            $controller = $module->getController($routeName);

            $controller->preload();
            $repository = $controller->getRepository();

            $query = $repository->getModel()->orderBy('updated_at', 'desc');
            // $query = DB::table($repository->getTable())->select('id', 'created_at', 'updated_at')->orderBy('updated_at', 'desc');

            $limit = 200;
            if ($this->option('limit')) {
                $limit = intval($this->option('limit')) > 0 ? intval($this->option('limit')) : null;

                if (! ($limit && $limit > 0)) {
                    $limit = 200;
                }
            }

            if ($this->option('eager')) {
                $query = $query->with($this->option('eager'));
            }

            $count = 0;
            $callback = function ($item, $key) use ($controller, &$count, $cacheFormItem, $cacheFormattedItem) {
                if ($cacheFormItem) {
                    $controller->getFormItem($item->id, withoutDefaultScopes: true, item: $item);
                }
                if ($cacheFormattedItem) {
                    $controller->getFormattedIndexItem($item);
                }
                $count++;
            };

            $query->chunk($limit, function ($items) use ($callback) {
                $items->each(function ($item, $key) use ($callback) {
                    $callback($item, $key);
                });
            }, 50);

            $this->line("  <fg=green>✓</> {$module->getName()} -> {$routeName}: Warmed {$count} item caches", verbosity: 'vv');
        } catch (\Exception $e) {
            $this->error("{$module->getName()} -> {$routeName}: Failed to warm caches: '{$e->getMessage()}'", verbosity: 'vv');
            $this->error($e->getTraceAsString(), verbosity: 'vvv');

            if (($logChannel = $this->getLogChannel())) {
                // if log channel is exists, log the error
                \Illuminate\Support\Facades\Log::channel($logChannel)->error('Cache warm ITEMS error: ' . $e->getMessage(), [
                    'module' => $module->getName(),
                    'routeName' => $routeName,
                    'exception' => $e->getTraceAsString(),
                ]);
            }
        }
    }

    /**
     * Warm cache for all modules.
     */
    protected function warmAllModulesCache(): void
    {
        $modules = Modularity::allEnabled();

        if (count($modules) === 0) {
            $this->warn('No enabled modules found.');

            return;
        }

        $this->info('Warming caches for all modules...');
        $this->newLine();

        foreach ($modules as $module) {
            $moduleName = $module->getStudlyName();

            if (! ModularityCache::isEnabled($module->getName())) {
                $this->line("  <fg=yellow>⚠</> {$module->getName()}: Caching is disabled", verbosity: 'v');

                continue;
            }
            $this->line("  <fg=green>✓</> {$moduleName}: Processing", verbosity: 'v');

            foreach ($module->getRouteNames() as $routeName) {
                if (! ModularityCache::isEnabled($module->getName(), $routeName)) {
                    $this->line("  <fg=yellow>⚠</> {$module->getName()} -> {$routeName}: Caching is disabled", verbosity: 'v');

                    continue;
                } else {
                    $this->line("  <fg=green>✓</> {$module->getName()} -> {$routeName}: Caching is enabled", verbosity: 'v');
                }

                if (ModularityCache::isEnabled($module->getName(), $routeName, 'counts')) {
                    $this->warmModuleCounts($module, $routeName);
                } else {
                    $this->line("  <fg=yellow>⚠</> {$module->getName()} -> {$routeName}: Counts cache is disabled", verbosity: 'v');
                }
                $this->warmModuleItems($module, $routeName);
            }
        }

        $this->newLine();
        $this->info('Cache warming completed for all modules.');
    }
}
