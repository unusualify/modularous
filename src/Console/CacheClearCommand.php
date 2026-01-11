<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Support\Str;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Facades\ModularityCache;

class CacheClearCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularity:cache:clear
                            {module? : The module name to clear cache for}
                            {routeName? : The route name to clear cache for}
                            {--counts : Clear only count caches}
                            {--index : Clear only index/list caches}
                            {--records : Clear only record caches}
                            {--formattedItems : Clear only formatted item caches}
                            {--formItems : Clear only form item caches}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear modularity caches for all or specific modules';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $module = $this->argument('module');
        $routeName = $this->argument('routeName');
        $countsOnly = $this->option('counts');
        $indexOnly = $this->option('index');
        $recordsOnly = $this->option('records');
        $formattedItemsOnly = $this->option('formattedItems');
        $formItemsOnly = $this->option('formItems');
        // Check if caching is enabled
        if (! ModularityCache::isEnabled()) {
            $this->warn('Modularity caching is disabled.');

            return 1;
        }

        // Determine what to clear
        $clearAll = ! $countsOnly && ! $indexOnly && ! $recordsOnly;

        if ($module && $routeName) {
            $this->clearModuleCache(Str::studly($module), Str::studly($routeName), $clearAll, $countsOnly, $indexOnly, $recordsOnly, $formattedItemsOnly, $formItemsOnly);
        }elseif ($module) {
            $this->clearModuleCache(Str::studly($module), null, $clearAll, $countsOnly, $indexOnly, $recordsOnly, $formattedItemsOnly, $formItemsOnly);
        } else {
            $this->clearAllModulesCache($clearAll, $countsOnly, $indexOnly, $recordsOnly, $formattedItemsOnly, $formItemsOnly);
        }

        return 0;
    }

    /**
     * Clear cache for a specific module.
     */
    protected function clearModuleCache(
        string $module,
        ?string $routeName = null,
        bool $clearAll,
        bool $countsOnly,
        bool $indexOnly,
        bool $recordsOnly,
        bool $formattedItemsOnly,
        bool $formItemsOnly
    ): void {
        $this->info("Clearing cache for module: {$module}");
        if ($routeName) {
            $this->info("Clearing cache for route: {$routeName}");
        }

        if ($clearAll) {
            ModularityCache::invalidateModule($module, $routeName);
            $this->line("  <fg=green>✓</> All caches cleared for {$module}");

            return;
        }

        if ($countsOnly) {
            ModularityCache::invalidateCountCaches($module, $routeName);
            $this->line("  <fg=green>✓</> Count caches cleared for {$module}");
        }

        if ($indexOnly) {
            ModularityCache::invalidateIndexCaches($module, $routeName);
            // ModularityCache::invalidateIndexResponseCaches($module, $routeName);
            $this->line("  <fg=green>✓</> Index caches cleared for {$module}");
        }

        if ($recordsOnly) {
            $prefix = ModularityCache::getPrefix();
            $pattern = "{$prefix}:{$module}:" . ($routeName ?: '*') . ":record:*";
            $count = ModularityCache::invalidateByPattern($pattern);
            $this->line("  <fg=green>✓</> {$count} record caches cleared for {$module}");
        }

        if ($formattedItemsOnly) {
            $prefix = ModularityCache::getPrefix();
            $pattern = "{$prefix}:{$module}:" . ($routeName ?: '*') . ":formattedItem:*";
            $count = ModularityCache::invalidateByPattern($pattern);
            $this->line("  <fg=green>✓</> {$count} formatted item caches cleared for {$module}");
        }

        if ($formItemsOnly) {
            $prefix = ModularityCache::getPrefix();
            $pattern = "{$prefix}:{$module}:" . ($routeName ?: '*') . ":formItem:*";
            $count = ModularityCache::invalidateByPattern($pattern);
            $this->line("  <fg=green>✓</> {$count} form item caches cleared for {$module}");
        }
    }

    /**
     * Clear cache for all modules.
     */
    protected function clearAllModulesCache(
        bool $clearAll,
        bool $countsOnly,
        bool $indexOnly,
        bool $recordsOnly,
        bool $formattedItemsOnly,
        bool $formItemsOnly
    ): void {
        if ($clearAll) {
            $this->info('Clearing all modularity caches...');
            ModularityCache::flush();
            $this->line('<fg=green>✓</> All modularity caches cleared');

            return;
        }

        $modules = Modularity::allEnabled();

        if ($modules->isEmpty()) {
            $this->warn('No enabled modules found.');

            return;
        }

        $this->info('Clearing caches for all modules...');

        foreach ($modules as $module) {
            $moduleName = $module->getStudlyName();

            if ($countsOnly) {
                ModularityCache::invalidateCountCaches($moduleName);
                $this->line("  <fg=green>✓</> Count caches cleared for {$moduleName}");
            }

            if ($indexOnly) {
                ModularityCache::invalidateIndexCaches($moduleName);
                // ModularityCache::invalidateIndexResponseCaches($moduleName);
                $this->line("  <fg=green>✓</> Index caches cleared for {$moduleName}");
            }

            if ($recordsOnly) {
                $prefix = ModularityCache::getPrefix();
                $pattern = "{$prefix}:{$moduleName}:*:record:*";
                $count = ModularityCache::invalidateByPattern($pattern);
                $this->line("  <fg=green>✓</> {$count} record caches cleared for {$moduleName}");
            }

            if ($formattedItemsOnly) {
                $prefix = ModularityCache::getPrefix();
                $pattern = "{$prefix}:{$moduleName}:*:formattedItem:*";
                $count = ModularityCache::invalidateByPattern($pattern);
                $this->line("  <fg=green>✓</> {$count} formatted item caches cleared for {$moduleName}");
            }

            if ($formItemsOnly) {
                $prefix = ModularityCache::getPrefix();
                $pattern = "{$prefix}:{$moduleName}:*:formItem:*";
                $count = ModularityCache::invalidateByPattern($pattern);
                $this->line("  <fg=green>✓</> {$count} form item caches cleared for {$moduleName}");
            }
        }

        $this->newLine();
        $this->info('Cache clearing completed.');
    }
}

