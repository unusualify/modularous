---
sidebarPos: 17
sidebarTitle: RelationshipGraph
---

# RelationshipGraph

**Facade**: `Unusualify\Modularous\Facades\RelationshipGraph`  
**Accessor**: `modularous.relationship.graph`  
**Underlying**: `Unusualify\Modularous\Services\CacheRelationshipGraph`

Builds and queries the model → module-route dependency graph used by `ModularousCache` to invalidate the right cache entries when a related model changes. See [CacheRelationshipGraph](/system-reference/backend/services/cache-relationship-graph) for implementation details.

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `isEnabled` | `(): bool` | Whether relationship graph tracking is enabled |
| `getGraph` | `(): array` | Returns the full cached graph |
| `buildGraph` | `(): array` | Builds the graph and stores it in cache |
| `rebuildGraph` | `(): array` | Forces a fresh rebuild, ignoring the cached version |
| `clearGraph` | `(): void` | Removes the graph from cache |
| `isCached` | `(): bool` | Whether the graph is currently cached |
| `getAffectedSubmodules` | `(string $modelClass): array` | Returns module routes affected by a model class |
| `getAffectedSubmodulesByTable` | `(string $tableName): array` | Returns module routes affected by a table name |
| `analyzeImpact` | `(string $modelOrTable): array` | Full impact analysis for a model class or table name |
| `getStats` | `(): array` | Returns graph statistics (node count, edge count, etc.) |
| `getVisualGraph` | `(): array` | Returns graph data formatted for visualization |

## Usage

```php
use Unusualify\Modularous\Facades\RelationshipGraph;

// Find which routes cache should be invalidated when a User is updated
$affected = RelationshipGraph::getAffectedSubmodules(\App\Models\User::class);
// → [['module' => 'Blog', 'route' => 'posts'], ...]

// Force a rebuild after adding new module relationships
RelationshipGraph::rebuildGraph();
```
