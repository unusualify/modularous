<?php

namespace Unusualify\Modularity\Console;

use Unusualify\Modularity\Facades\RelationshipGraph;
use Unusualify\Modularity\Facades\ModularityCache;

class CacheStatsCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularity:cache:stats
                            {module? : The module name to show stats for}
                            {--keys : Show individual cache keys}
                            {--deps : Show cache dependencies}
                            {--graph : Show relationship graph summary}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show modularity cache statistics';

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
        $showKeys = $this->option('keys');
        $showDeps = $this->option('deps');
        $showGraph = $this->option('graph');

        // Check if caching is enabled
        $this->info('Modularity Cache Status');
        $this->line('========================');
        $this->newLine();

        $this->line('Enabled: ' . (ModularityCache::isEnabled() ? '<fg=green>Yes</>' : '<fg=red>No</>'));
        $this->line('Driver: ' . ModularityCache::getDriver());
        $this->line('Prefix: ' . ModularityCache::getPrefix());
        $this->line('Tags Support: ' . (ModularityCache::usesTags() ? '<fg=green>Yes</>' : '<fg=yellow>No</>'));

        $this->newLine();

        // Get TTL settings
        $this->info('TTL Settings (seconds):');
        $this->line('  - Counts: ' . ModularityCache::getTtl('counts'));
        $this->line('  - Index: ' . ModularityCache::getTtl('index'));
        $this->line('  - Record: ' . ModularityCache::getTtl('record'));
        $this->line('  - Response: ' . ModularityCache::getTtl('response:json'));

        $this->newLine();

        // Show relationship graph summary
        if ($showGraph) {
            $this->showGraphSummary();
        }

        // Show dependencies
        if ($showDeps) {
            $this->showDependencies();
        }

        // Get cache statistics
        $stats = ModularityCache::getStats($module);

        if (isset($stats['error'])) {
            $this->error('Error fetching stats: ' . $stats['error']);

            return 1;
        }

        $this->info('Cache Statistics' . ($module ? " for module: {$module}" : ''));
        $this->line('Total keys: ' . $stats['keys_count']);
        $this->line('Using tags: ' . ($stats['using_tags'] ?? false ? 'Yes' : 'No'));

        if ($showKeys && ! empty($stats['keys'])) {
            $this->newLine();
            $this->info('Cache Keys:');

            // Group keys by type
            $grouped = $this->groupKeysByType($stats['keys']);

            foreach ($grouped as $type => $keys) {
                $this->newLine();
                $this->line("<fg=cyan>{$type}</> (" . count($keys) . ' keys):');
                foreach ($keys as $key) {
                    $this->line("  - {$key}");
                }
            }
        }

        return 0;
    }

    /**
     * Show relationship graph summary.
     */
    protected function showGraphSummary(): void
    {
        $this->info('Relationship Graph (Auto-Discovery):');

        if (! RelationshipGraph::isEnabled()) {
            $this->line('  <fg=yellow>Graph is disabled</>');
            $this->line('  Enable with MODULARITY_RESOURCE_CACHE_GRAPH_ENABLED=true');
            $this->newLine();

            return;
        }

        $stats = RelationshipGraph::getStats();

        $this->line('  Status: ' . ($stats['cached'] ? '<fg=green>Cached</>' : '<fg=yellow>Not cached</>'));
        $this->line('  TTL: ' . number_format($stats['ttl']) . ' seconds');
        $this->line('  Models tracked: ' . $stats['total_models_tracked']);
        $this->line('  Tables tracked: ' . $stats['total_tables_tracked']);
        $this->line('  Modules with relationships: ' . $stats['total_modules_with_relationships']);

        if (! empty($stats['model_to_modules'])) {
            $this->newLine();
            $this->line('  <fg=cyan>Top affected models:</>');
            $sorted = $stats['model_to_modules'];
            uasort($sorted, fn ($a, $b) => count($b) <=> count($a));
            $top5 = array_slice($sorted, 0, 5, true);

            foreach ($top5 as $model => $modules) {
                $shortName = class_basename($model);
                $this->line("    {$shortName} => " . implode(', ', $modules));
            }
        }

        $this->newLine();
        $this->line('<fg=gray>Run `php artisan modularity:cache:graph stats` for full details</>');
        $this->newLine();
    }

    /**
     * Show cache dependency configuration.
     */
    protected function showDependencies(): void
    {
        $dependencies = config('modularity.cache.dependencies', []);

        $this->info('Cache Dependencies (Manual Config):');

        if (empty($dependencies)) {
            $this->line('  <fg=yellow>No manual dependencies configured</>');
            $this->line('  Configure in config/modularity.cache.dependencies');
        } else {
            foreach ($dependencies as $model => $modules) {
                $moduleList = implode(', ', $modules);
                $this->line("  <fg=cyan>{$model}</> => [{$moduleList}]");
            }
        }

        $this->newLine();
        $this->line('<fg=gray>Manual dependencies are merged with auto-discovered graph dependencies.</>');
        $this->line('<fg=gray>Use --graph to see auto-discovered relationships.</>');
        $this->newLine();
    }

    /**
     * Group cache keys by type.
     */
    protected function groupKeysByType(array $keys): array
    {
        $grouped = [];

        foreach ($keys as $key) {
            // Parse key: prefix:module:type:hash
            $parts = explode(':', $key);

            if (count($parts) >= 3) {
                $type = $parts[2] ?? 'unknown';
                $grouped[$type][] = $key;
            } else {
                $grouped['other'][] = $key;
            }
        }

        ksort($grouped);

        return $grouped;
    }
}

