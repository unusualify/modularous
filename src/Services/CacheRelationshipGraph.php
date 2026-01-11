<?php

namespace Unusualify\Modularity\Services;

use Illuminate\Support\Facades\Cache;
use Unusualify\Modularity\Facades\Modularity;

class CacheRelationshipGraph
{
    /**
     * Cache key for the relationship graph.
     */
    protected const GRAPH_CACHE_KEY = 'modularity:cache:relationship_graph';

    /**
     * The relationship graph.
     * Structure: [
     *   'model_class' => ['module1', 'module2', ...],
     *   'pivot_table' => ['module1', ...],
     * ]
     */
    protected ?array $graph = null;

    /**
     * Inverse graph: module => [model_classes that belong to it]
     */
    protected ?array $moduleModels = null;

    /**
     * Check if the relationship graph feature is enabled.
     */
    public function isEnabled(): bool
    {
        return config('modularity.cache.graph.enabled', true);
    }

    /**
     * Get the TTL for the graph cache.
     */
    protected function getGraphTtl(): int
    {
        return (int) config('modularity.cache.graph.ttl', 86400);
    }

    /**
     * Get submodules that should be invalidated when a model changes.
     * Returns submodule names (which correspond to model/route names).
     */
    public function getAffectedModuleRoutes(string $modelClass): array
    {
        if (! $this->isEnabled()) {
            return [];
        }

        $graph = $this->getGraph();

        $dependents = [];

        $moduleRoutes = $graph['model_to_module_routes'][$modelClass] ?? [];

        foreach ($moduleRoutes as $moduleData) {
            // $dependents[] = $graph['submodule_to_module'][$moduleData['moduleRouteName']] ?? null;
            $dependents[] = [$moduleData['moduleName'], $moduleData['moduleRouteName']];
        }

        return $dependents;
    }

    /**
     * Get submodules that should be invalidated when a pivot table record changes.
     */
    public function getAffectedModuleRoutesByTable(string $tableName): array
    {
        if (! $this->isEnabled()) {
            return [];
        }

        $graph = $this->getGraph();

        $moduleRoutes = $graph['table_to_module_routes'][$tableName] ?? [];

        foreach ($moduleRoutes as $moduleData) {
            $dependents[] = [$moduleData['moduleName'], $moduleData['moduleRouteName']];
        }

        return $dependents;
    }

    /**
     * Get the full relationship graph.
     */
    public function getGraph(): array
    {
        if ($this->graph !== null) {
            return $this->graph;
        }

        if (! $this->isEnabled()) {
            return $this->getEmptyGraph();
        }

        // Try to get from cache
        $this->graph = Cache::get(self::GRAPH_CACHE_KEY);

        if ($this->graph === null) {
            $this->graph = $this->buildGraph();
            $this->cacheGraph();
        }

        return $this->graph;
    }

    /**
     * Get an empty graph structure.
     */
    protected function getEmptyGraph(): array
    {
        return [
            'model_to_module_routes' => [],
            'table_to_module_routes' => [],
            'module_relationships' => [],
            'submodule_to_module' => [], // Maps submodule name to parent module name
        ];
    }

    /**
     * Build the relationship graph from all modules.
     * The graph is submodule-centric: each submodule (route/model) is tracked independently.
     */
    public function buildGraph(): array
    {
        $graph = [
            'model_to_module_routes' => [],    // Model class => [submodules that display this model's data]
            'table_to_module_routes' => [],    // Table name => [submodules that use this table]
            'module_relationships' => [], // Submodule => [relationship details]
            'submodule_to_module' => [],    // Submodule name => Parent module name
        ];

        $modules = Modularity::allEnabled();

        foreach ($modules as $module) {
            $moduleName = $module->getStudlyName();
            $this->processModuleSubmodules($module, $moduleName, $graph);
        }

        return $graph;
    }

    /**
     * Process all submodules (entities/routes) within a module.
     * A module can have multiple submodules, each with its own model.
     */
    protected function processModuleSubmodules($module, string $moduleName, array &$graph): void
    {
        // Get ALL entity model classes for this module (each represents a submodule)
        $modelClasses = $this->getAllModuleModelClasses($module);

        if (empty($modelClasses)) {
            return;
        }

        foreach ($modelClasses as $modelClass) {
            $this->processSubmoduleRelationships($modelClass, $moduleName, $graph);
        }
    }

    /**
     * Process relationships for a single submodule (entity/model).
     * Submodule name = Model class basename (e.g., PressReleasePackage)
     */
    protected function processSubmoduleRelationships(string $modelClass, string $parentModuleName, array &$graph): void
    {
        if (! class_exists($modelClass)) {
            return;
        }

        try {
            $model = new $modelClass;

            // Check if model has getEloquentRelationships method
            if (! method_exists($model, 'getEloquentRelationships')) {
                return;
            }

            $relationships = $model->getEloquentRelationships();

            $model = new $modelClass;

            if(!method_exists($model, 'getModuleName') || !method_exists($model, 'getRouteName')) {
                return;
            }

            $moduleName = $model->getModuleName();
            $moduleRouteName = $model->getRouteName();
            // // Submodule name is the model class basename
            // $submoduleName = class_basename($modelClass);

            // Map submodule to its parent module
            // $graph['submodule_to_module'][$submoduleName] = $parentModuleName;
            $graph['submodule_to_module'][] = [
                'moduleName' => $moduleName,
                'moduleRouteName' => $moduleRouteName,
            ];

            // Initialize submodule relationships array
            if (! isset($graph['module_relationships'][$moduleName][$moduleRouteName])) {
                if(!isset($graph['module_relationships'][$moduleName])) {
                    $graph['module_relationships'][$moduleName] = [];
                }
                $graph['module_relationships'][$moduleName][$moduleRouteName] = [
                    'model_class' => $modelClass,
                    'parent_module' => $parentModuleName,
                    'relationships' => $relationships,
                ];
            }

            if (empty($relationships)) {
                return;
            }

            foreach ($relationships as $relationName => $relationData) {
                $this->processRelationship($moduleName, $moduleRouteName, $relationName, $relationData, $graph);
            }
        } catch (\Throwable $e) {
            // Log but don't fail - some models might not be instantiable
            logger()->debug("CacheRelationshipGraph: Could not process {$modelClass}: " . $e->getMessage());
        }
    }

    /**
     * Process a single relationship for a submodule.
     */
    protected function processRelationship($moduleName, $moduleRouteName, $relationName, array $relationData, array &$graph): void
    {
        $relatedModel = $relationData['relationship_model'] ?? null;
        $relatedTable = $relationData['relationship_table'] ?? null;
        $middlemanModel = $relationData['middleman_model'] ?? null;
        $middlemanTable = $relationData['middleman_table'] ?? null;
        $hasMiddleman = $relationData['has_middleman'] ?? false;

        // Store relationship info for the submodule
        $graph['module_relationships'][$moduleName][$moduleRouteName]['relationships'][$relationName] = [
            'model' => $relatedModel,
            'table' => $relatedTable,
            'type' => $relationData['relationship_class'] ?? 'Unknown',
        ];

        // Map: when related model changes, invalidate this submodule's cache
        if ($relatedModel) {
            if (! isset($graph['model_to_module_routes'][$relatedModel])) {
                $graph['model_to_module_routes'][$relatedModel] = [];
            }
            if (! in_array([
                'moduleName' => $moduleName,
                'moduleRouteName' => $moduleRouteName,
            ], $graph['model_to_module_routes'][$relatedModel])) {
                $graph['model_to_module_routes'][$relatedModel][] = [
                    'moduleName' => $moduleName,
                    'moduleRouteName' => $moduleRouteName,
                ];
            }
        }

        // Map: when related table changes, invalidate this submodule's cache
        if ($relatedTable) {
            if (! isset($graph['table_to_module_routes'][$relatedTable])) {
                $graph['table_to_module_routes'][$relatedTable] = [];
            }
            if (! in_array([
                'moduleName' => $moduleName,
                'moduleRouteName' => $moduleRouteName,
            ], $graph['table_to_module_routes'][$relatedTable])) {
                $graph['table_to_module_routes'][$relatedTable][] = [
                    'moduleName' => $moduleName,
                    'moduleRouteName' => $moduleRouteName,
                ];
            }
        }

        // Handle middleman/pivot models (BelongsToMany, HasOneThrough, etc.)
        if ($hasMiddleman) {
            if ($middlemanModel) {
                if (! isset($graph['model_to_module_routes'][$middlemanModel])) {
                    $graph['model_to_module_routes'][$middlemanModel] = [];
                }
                if (! in_array([
                    'moduleName' => $moduleName,
                    'moduleRouteName' => $moduleRouteName,
                ], $graph['model_to_module_routes'][$middlemanModel])) {
                    $graph['model_to_module_routes'][$middlemanModel][] = [
                        'moduleName' => $moduleName,
                        'moduleRouteName' => $moduleRouteName,
                    ];
                }
            }

            if ($middlemanTable) {
                if (! isset($graph['table_to_module_routes'][$middlemanTable])) {
                    $graph['table_to_module_routes'][$middlemanTable] = [];
                }
                if (! in_array([
                    'moduleName' => $moduleName,
                    'moduleRouteName' => $moduleRouteName,
                ], $graph['table_to_module_routes'][$middlemanTable])) {
                    $graph['table_to_module_routes'][$middlemanTable][] = [
                        'moduleName' => $moduleName,
                        'moduleRouteName' => $moduleRouteName,
                    ];
                }
            }
        }
    }

    /**
     * Get all entity model classes for a module.
     * Scans the Entities folder for all model classes.
     */
    protected function getAllModuleModelClasses($module): array
    {
        $moduleName = $module->getStudlyName();
        $modulePath = $module->getPath();
        $modelClasses = [];

        // Check Entities folder
        $entitiesPath = $modulePath . '/Entities';
        if (is_dir($entitiesPath)) {
            $modelClasses = array_merge(
                $modelClasses,
                $this->scanDirectoryForModels($entitiesPath, "Modules\\{$moduleName}\\Entities")
            );
        }

        // Check Models folder (alternative location)
        $modelsPath = $modulePath . '/Models';
        if (is_dir($modelsPath)) {
            $modelClasses = array_merge(
                $modelClasses,
                $this->scanDirectoryForModels($modelsPath, "Modules\\{$moduleName}\\Models")
            );
        }

        return array_unique($modelClasses);
    }

    /**
     * Scan a directory for model class files.
     */
    protected function scanDirectoryForModels(string $directory, string $namespace): array
    {
        $models = [];

        $files = glob($directory . '/*.php');

        foreach ($files as $file) {
            $className = pathinfo($file, PATHINFO_FILENAME);
            $fullClass = $namespace . '\\' . $className;

            // Check if class exists and is a model (not a trait, interface, etc.)
            if (class_exists($fullClass)) {
                $reflection = new \ReflectionClass($fullClass);

                // Skip abstract classes, interfaces, and traits
                if (! $reflection->isAbstract() && ! $reflection->isInterface() && ! $reflection->isTrait()) {
                    $models[] = $fullClass;
                }
            }
        }

        return $models;
    }

    /**
     * Cache the relationship graph.
     */
    protected function cacheGraph(): void
    {
        Cache::put(self::GRAPH_CACHE_KEY, $this->graph, $this->getGraphTtl());
    }

    /**
     * Clear and rebuild the relationship graph.
     */
    public function rebuildGraph(): array
    {
        Cache::forget(self::GRAPH_CACHE_KEY);
        $this->graph = null;

        return $this->getGraph();
    }

    /**
     * Clear the cached graph.
     */
    public function clearGraph(): void
    {
        Cache::forget(self::GRAPH_CACHE_KEY);
        $this->graph = null;
    }

    /**
     * Get statistics about the graph.
     */
    public function getStats(): array
    {
        $graph = $this->getGraph();

        return [
            'enabled' => $this->isEnabled(),
            'cached' => $this->isCached(),
            'ttl' => $this->getGraphTtl(),
            'total_models_tracked' => count($graph['model_to_module_routes'] ?? []),
            'total_tables_tracked' => count($graph['table_to_module_routes'] ?? []),

            // 'total_submodules' => count($graph['submodule_relationships'] ?? []),
            'total_module_routes' => array_reduce($graph['module_relationships'] ?? [], fn($carry, $moduleData) => $carry + count($moduleData), 0),

            'model_to_module_routes' => array_map(fn($modelData) => array_map(fn($item) => $item['moduleName'] . '->' . $item['moduleRouteName'], $modelData), $graph['model_to_module_routes'] ?? []),
            'table_to_module_routes' => array_map(fn($tableData) => array_map(fn($item) => $item['moduleName'] . '->' . $item['moduleRouteName'], $tableData), $graph['table_to_module_routes'] ?? []),
            'submodule_to_module' => $graph['submodule_to_module'] ?? [],
        ];
    }

    /**
     * Check if the graph is currently cached.
     */
    public function isCached(): bool
    {
        return Cache::has(self::GRAPH_CACHE_KEY);
    }

    /**
     * Find which submodules are affected by changes to a specific model or table.
     * Returns a comprehensive analysis.
     */
    public function analyzeImpact(string $modelOrTable): array
    {
        $graph = $this->getGraph();

        $result = [
            'input' => $modelOrTable,
            'type' => null,
            'affected_module_routes' => [],
        ];

        // Check if it's a model class
        if (isset($graph['model_to_module_routes'][$modelOrTable])) {
            $result['type'] = 'model';
            // $result['affected_module_routes'] = array_map(fn($item) => $item['moduleName'] . '::' . $item['moduleRouteName'], $graph['model_to_module_routes'][$modelOrTable]);
            $result['affected_module_routes'] = $graph['model_to_module_routes'][$modelOrTable];

            return $result;
        }

        // Check if it's a short model name
        // foreach ($graph['model_to_module_routes'] ?? [] as $fullClass => $moduleRoutes) {
        //     if (class_basename($fullClass) === $modelOrTable) {
        //         $result['type'] = 'model';
        //         $result['full_class'] = $fullClass;
        //         // $result['affected_module_routes'] = array_map(fn($item) => $item['moduleName'] . '::' . $item['moduleRouteName'], $moduleRoutes);
        //         $result['affected_module_routes'] = $moduleRoutes;

        //         return $result;
        //     }
        // }

        // Check if it's a table name
        if (isset($graph['table_to_module_routes'][$modelOrTable])) {
            $result['type'] = 'table';
            // $result['affected_module_routes'] = array_map(fn($item) => $item['moduleName'] . '::' . $item['moduleRouteName'], $graph['table_to_module_routes'][$modelOrTable]);
            $result['affected_module_routes'] = $graph['table_to_module_routes'][$modelOrTable];

            return $result;
        }

        return $result;
    }

    /**
     * Get a visual representation of the graph for debugging.
     * Organized by submodule (not by module).
     */
    public function getVisualGraph(): array
    {
        $graph = $this->getGraph();
        $visual = [];

        // Group submodules by their parent module for display
        $moduleGroups = [];
        foreach($graph['module_relationships'] ?? [] as $moduleName => $moduleData) {
            if (! isset($moduleGroups[$moduleName])) {
                $moduleGroups[$moduleName] = [];
            }
            foreach($moduleData as $moduleRouteName => $relationData) {
                $moduleGroups[$moduleName][$moduleRouteName] = $relationData;
            }
        }

        foreach ($moduleGroups as $moduleName => $moduleRoutes) {
            $visual[$moduleName] = [
                // 'submodules' => [],
                'module_routes' => [],
            ];

            foreach ($moduleRoutes as $moduleRouteName => $moduleRouteData) {
                $relationships = $moduleRouteData['relationships'] ?? [];

                $visual[$moduleName]['module_routes'][$moduleRouteName] = [
                    'model_class' => $moduleRouteData['model_class'] ?? 'Unknown',
                    'relationships' => [],
                    'affected_by' => [],
                ];

                foreach ($relationships as $relationName => $relationData) {
                    if (! is_array($relationData)) {
                        continue;
                    }

                    $visual[$moduleName]['module_routes'][$moduleRouteName]['relationships'][$relationName] = [
                        'model' => $relationData['model'] ?? 'Unknown',
                        'type' => class_basename($relationData['type'] ?? 'Unknown'),
                    ];
                }

                // Add affected_by for this submodule
                foreach ($graph['model_to_module_routes'] ?? [] as $model => $affectedModuleRoutes) {
                    if (in_array([
                        'moduleName' => $moduleName,
                        'moduleRouteName' => $moduleRouteName,
                    ], $affectedModuleRoutes)) {
                        $modelBasename = class_basename($model);
                        if (! in_array($modelBasename, $visual[$moduleName]['module_routes'][$moduleRouteName]['affected_by'])) {
                            $visual[$moduleName]['module_routes'][$moduleRouteName]['affected_by'][] = $modelBasename;
                        }
                    }
                }
            }
        }

        return $visual;
    }
}

