---
sidebarPos: 10
sidebarTitle: CacheRelationshipGraph
---

# CacheRelationshipGraph

**File**: `src/Services/CacheRelationshipGraph.php`  
**Cache key**: `modularity:cache:relationship_graph`

`CacheRelationshipGraph` builds and caches a **dependency map** between Eloquent models and the module routes that display their data. When a model record changes, the cache service consults this graph to know exactly which module route caches need to be invalidated — instead of flushing everything.

## Configuration

```php
// config/modularity.php
'cache' => [
    'graph' => [
        'enabled' => true,
        'ttl'     => 86400,   // seconds (24 hours)
    ],
],
```

## Graph Structure

```php
[
    'model_to_module_routes' => [
        'Modules\Orders\Entities\Order' => [
            ['moduleName' => 'Orders', 'moduleRouteName' => 'order'],
            ['moduleName' => 'Dashboard', 'moduleRouteName' => 'summary'],
        ],
    ],
    'table_to_module_routes' => [
        'order_tags' => [
            ['moduleName' => 'Orders', 'moduleRouteName' => 'order'],
        ],
    ],
    'module_relationships' => [
        'Orders' => [
            'order' => [
                'model_class'  => 'Modules\Orders\Entities\Order',
                'relationships' => [...],
            ],
        ],
    ],
    'submodule_to_module' => [...],
]
```

## Key Methods

| Method | Description |
|--------|-------------|
| `getGraph(): array` | Returns the full graph (from cache or builds it) |
| `buildGraph(): array` | Scans all enabled modules and their entity relationships |
| `getAffectedModuleRoutes(string $modelClass): array` | Returns `[moduleName, moduleRouteName]` pairs affected by a model change |
| `getAffectedModuleRoutesByTable(string $tableName): array` | Same, keyed by raw table name (for pivot tables) |
| `rebuildGraph(): array` | Clears the cached graph and rebuilds it |
| `clearGraph(): void` | Deletes the cached graph without rebuilding |
| `getStats(): array` | Returns counts and mappings for debugging |
| `analyzeImpact(string $modelOrTable): array` | Given a model class or table name, returns which module routes are affected |
| `getVisualGraph(): array` | Returns a human-readable nested structure for debugging |
| `isCached(): bool` | Checks if the graph is currently in the cache store |
| `isEnabled(): bool` | Checks the `cache.graph.enabled` config flag |

## How the Graph is Built

`buildGraph()` iterates over all enabled modules via `Modularity::allEnabled()`. For each module, it scans the `Entities/` and `Models/` directories for concrete model classes. For each model:

1. Calls `getEloquentRelationships()` on the model instance.
2. Records the `relationship_model`, `relationship_table`, `middleman_model`, `middleman_table` from each relation.
3. Populates `model_to_module_routes` and `table_to_module_routes` so invalidation can work both by model class and by raw table name.

Models that do not implement `getEloquentRelationships()`, `getModuleName()`, or `getRouteName()` are skipped silently.

## Usage in Cache Invalidation

```php
// When Order model is saved:
$affectedRoutes = $graph->getAffectedModuleRoutes(Order::class);
// [['moduleName' => 'Orders', 'moduleRouteName' => 'order'], ...]

foreach ($affectedRoutes as [$module, $route]) {
    $cacheService->invalidateModuleRoute($module, $route);
}
```

## Debugging

```php
// Inspect what models are tracked and their dependencies
$stats = app(CacheRelationshipGraph::class)->getStats();

// Get a visual breakdown per module
$visual = app(CacheRelationshipGraph::class)->getVisualGraph();

// Check impact of a specific model
$impact = app(CacheRelationshipGraph::class)->analyzeImpact(Order::class);
```

## Notes

- The graph is cached for `cache.graph.ttl` seconds (default 24 hours). Run `cache:clear` or `rebuildGraph()` after adding new module relationships.
- If `isEnabled()` returns `false`, `getAffectedModuleRoutes()` and `getAffectedModuleRoutesByTable()` both return `[]`, disabling relationship-based invalidation entirely.
