<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Console\Command;
use Unusualify\Modularity\Facades\RelationshipGraph;

class CacheGraphCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'modularity:cache:graph
                            {action=show : Action to perform: show, rebuild, stats, analyze}
                            {--module= : Show graph for a specific module}
                            {--model= : Analyze impact for a specific model/table}
                            {--format=table : Output format: table, json}';

    /**
     * The console command description.
     */
    protected $description = 'Manage the modularity cache relationship graph';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! RelationshipGraph::isEnabled()) {
            $this->warn('⚠️  Cache relationship graph is disabled.');
            $this->line('Enable it by setting MODULARITY_RESOURCE_CACHE_GRAPH_ENABLED=true');

            return 1;
        }

        $action = $this->argument('action');

        return match ($action) {
            'show' => $this->showGraph(),
            'rebuild' => $this->rebuildGraph(),
            'stats' => $this->showStats(),
            'analyze' => $this->analyzeImpact(),
            default => $this->error("Unknown action: {$action}") ?? 1,
        };
    }

    /**
     * Show the relationship graph (submodule-centric).
     */
    protected function showGraph(): int
    {
        $this->info('📊 Cache Relationship Graph (Route-Centric)');
        $this->newLine();

        $visual = RelationshipGraph::getVisualGraph();
        $module = $this->option('module');

        if ($module) {
            if (! isset($visual[$module])) {
                $this->error("Module '{$module}' not found in the graph.");

                return 1;
            }
            $visual = [$module => $visual[$module]];
        }

        if ($this->option('format') === 'json') {
            $this->line(json_encode($visual, JSON_PRETTY_PRINT));

            return 0;
        }

        foreach ($visual as $moduleName => $data) {
            $moduleRouteCount = count($data['module_routes'] ?? []);
            $totalRelations = $this->countTotalRelations($data['module_routes'] ?? []);

            $this->components->twoColumnDetail(
                "<fg=cyan;options=bold>Module: {$moduleName}</>",
                "{$moduleRouteCount} module routes, {$totalRelations} relations"
            );

            // Show each submodule and its relationships
            if (! empty($data['module_routes'])) {
                foreach ($data['module_routes'] as $moduleRouteName => $moduleRouteData) {
                    $relationCount = count($moduleRouteData['relationships'] ?? []);
                    $this->line("  <fg=green>► Module Route: {$moduleRouteName}</> ({$relationCount} relations)");
                    $this->line("    <fg=gray>Model: {$moduleRouteData['model_class']}</>");

                    if (! empty($moduleRouteData['relationships'])) {
                        $rows = [];
                        foreach ($moduleRouteData['relationships'] as $relationName => $relationData) {
                            $rows[] = [
                                '    ' . $relationName,
                                $relationData['type'],
                                $relationData['model'],
                            ];
                        }

                        $this->table(
                            ['Relation', 'Type', 'Related Model'],
                            $rows
                        );
                    }

                    if (! empty($moduleRouteData['affected_by'])) {
                        $this->line('    <fg=yellow>Affected by:</> ' . implode(', ', array_unique($moduleRouteData['affected_by'])));
                    }

                    $this->newLine();
                }
            }
        }

        return 0;
    }

    /**
     * Count total relations across all submodules.
     */
    protected function countTotalRelations(array $moduleRoutes): int
    {
        $count = 0;
        foreach ($moduleRoutes as $moduleRouteData) {
            $count += count($moduleRouteData['relationships'] ?? []);
        }

        return $count;
    }

    /**
     * Rebuild the relationship graph.
     */
    protected function rebuildGraph(): int
    {
        $this->info('🔄 Rebuilding cache relationship graph (submodule-centric)...');

        $startTime = microtime(true);
        $graph = RelationshipGraph::rebuildGraph();
        $duration = round((microtime(true) - $startTime) * 1000, 2);

        $this->newLine();
        $this->components->twoColumnDetail('Models tracked', count($graph['model_to_submodules'] ?? []));
        $this->components->twoColumnDetail('Tables tracked', count($graph['table_to_submodules'] ?? []));
        $this->components->twoColumnDetail('Submodules scanned', count($graph['submodule_relationships'] ?? []));
        $this->components->twoColumnDetail('Build time', "{$duration}ms");

        $this->newLine();
        $this->components->info('Cache relationship graph rebuilt successfully.');

        return 0;
    }

    /**
     * Show statistics about the graph.
     */
    protected function showStats(): int
    {
        $this->info('📈 Cache Relationship Graph Statistics (Submodule-Centric)');
        $this->newLine();

        $stats = RelationshipGraph::getStats();

        $this->components->twoColumnDetail('Total models tracked', $stats['total_models_tracked']);
        $this->components->twoColumnDetail('Total tables tracked', $stats['total_tables_tracked']);
        $this->components->twoColumnDetail('Total module routes', $stats['total_module_routes'] ?? 0);

        $this->newLine();

        // Show model to submodules mapping
        if (! empty($stats['model_to_module_routes'])) {
            $this->info('Model → Module Routes Mapping:');
            $rows = [];
            foreach ($stats['model_to_module_routes'] as $model => $moduleRoute) {
                $rows[] = [
                    class_basename($model),
                    implode("\n", $moduleRoute),
                    count($moduleRoute),
                ];
            }

            // Sort by number of dependent submodules (descending)
            usort($rows, fn ($a, $b) => $b[2] <=> $a[2]);

            $this->table(
                ['Model', 'Affects Module Routes', 'Count'],
                array_slice($rows, 0, 20) // Show top 20
            );

            if (count($rows) > 20) {
                $this->line('  ... and ' . (count($rows) - 20) . ' more models');
            }
        }

        $this->newLine();

        // Show table to submodules mapping (for pivot tables)
        if (! empty($stats['table_to_module_routes'])) {
            $this->info('Table → Submodules Mapping (Pivot/Through tables):');
            $rows = [];
            foreach ($stats['table_to_module_routes'] as $table => $moduleRoute) {
                $rows[] = [
                    $table,
                    implode("\n", $moduleRoute),
                    count($moduleRoute),
                ];
            }

            // Sort by number of dependent submodules (descending)
            usort($rows, fn ($a, $b) => $b[2] <=> $a[2]);

            $this->table(
                ['Table', 'Affects Module Routes', 'Count'],
                array_slice($rows, 0, 20)
            );

            if (count($rows) > 20) {
                $this->line('  ... and ' . (count($rows) - 20) . ' more tables');
            }
        }

        $this->newLine();

        // Show submodule to module mapping
        if (! empty($stats['submodule_to_module'])) {
            $this->info('Module Route → Parent Module Mapping:');
            $rows = [];
            foreach ($stats['submodule_to_module'] as $moduleData) {
                $rows[] = [$moduleData['moduleRouteName'], $moduleData['moduleName']];
            }

            $this->table(
                ['Route', 'Module'],
                array_slice($rows, 0, 30)
            );

            if (count($rows) > 30) {
                $this->line('  ... and ' . (count($rows) - 30) . ' more submodules');
            }
        }

        return 0;
    }

    /**
     * Analyze impact of changes to a specific model or table.
     */
    protected function analyzeImpact(): int
    {
        $modelOrTable = $this->option('model');

        if (! $modelOrTable) {
            $this->error('Please provide a model or table name using --model=ModelName');

            return 1;
        }

        $this->info("🔍 Analyzing cache impact for: {$modelOrTable}");
        $this->newLine();

        $analysis = RelationshipGraph::analyzeImpact($modelOrTable);

        if (empty($analysis['affected_module_routes'])) {
            $this->warn("No module routes found that would be affected by changes to '{$modelOrTable}'");
            $this->newLine();
            $this->line('This could mean:');
            $this->line('  1. The model/table is not related to any cached module routes');
            $this->line('  2. The model name is incorrect');
            $this->line('  3. The relationship graph needs rebuilding: php artisan modularity:cache:graph rebuild');

            return 0;
        }

        $this->components->twoColumnDetail('Type', $analysis['type'] ?? 'unknown');

        if (isset($analysis['full_class'])) {
            $this->components->twoColumnDetail('Full Class', $analysis['full_class']);
        }

        $this->newLine();
        $this->info('Affected Module Routes:');

        foreach ($analysis['affected_module_routes'] as $moduleRoute) {
            // $parentModule = RelationshipGraph::getParentModule($submodule);
            $this->line("  <fg=green>→</> {$moduleRoute['moduleRouteName']} <fg=gray>(Module: {$moduleRoute['moduleName']})</>");
        }

        $this->newLine();
        $this->components->info(
            sprintf(
                'When %s is updated, %d module route cache(s) will be invalidated.',
                $modelOrTable,
                count($analysis['affected_module_routes'])
            )
        );

        return 0;
    }
}
