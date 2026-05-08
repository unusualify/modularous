---
sidebarPos: 9
sidebarTitle: ModularityCache
---

# ModularityCache

**Facade**: `Unusualify\Modularity\Facades\ModularityCache`  
**Accessor**: `modularity.cache`  
**Underlying**: `Unusualify\Modularity\Services\ModularityCacheService`

Full cache facade for Modularous — handles tag-based caching, TTL management, and targeted cache invalidation scoped to modules and route names. See [ModularityCacheService](/system-reference/backend/services/modularity-cache-service) for implementation details.

## Methods

### Configuration

| Method | Signature | Description |
|--------|-----------|-------------|
| `getDriver` | `(): string` | Returns the active cache driver name |
| `getPrefix` | `(): string` | Returns the cache key prefix |
| `isEnabled` | `(?string $module = null): bool` | Whether caching is enabled globally or for a module |
| `usesTags` | `(): bool` | Whether the driver supports cache tags |
| `getTtl` | `(string $type, ?string $module = null): int` | Returns TTL in seconds for the given type |

### Key Generation

| Method | Signature | Description |
|--------|-----------|-------------|
| `generateCacheKey` | `(string $module, string $routeName, string $type, array $params = []): string` | Builds a fully-qualified cache key |
| `generateRecordKey` | `(string $module, string $routeName, $id): string` | Builds a key for a single model record |
| `getModuleTags` | `(string $module): array` | Tags for an entire module |
| `getRouteTags` | `(string $module, string $routeName): array` | Tags for a specific module route |
| `getTypeTags` | `(string $module, string $routeName, string $type): array` | Tags for a type within a route |
| `generateRelationTag` | `(string $modelClass, $id): string` | Tag for a single model instance |
| `generateRelationTags` | `(array $relations): array` | Tags for multiple model instances |

### Read / Write

| Method | Signature | Description |
|--------|-----------|-------------|
| `remember` | `(string $key, int $ttl, Closure $callback, ...): mixed` | Cache or compute a value with TTL |
| `rememberForever` | `(string $key, Closure $callback, ...): mixed` | Cache a value without expiry |
| `rememberWithRelations` | `(string $key, int $ttl, Closure $callback, ..., array $relations): mixed` | Cache with relation tags |
| `get` | `(string $key, $default = null, ...): mixed` | Retrieve a cached value |
| `put` | `(string $key, $value, int $ttl, ...): bool` | Store a value with TTL |
| `putWithRelations` | `(string $key, $value, int $ttl, ..., array $relations): bool` | Store with relation tags |
| `has` | `(string $key, ...): bool` | Check if a key exists |
| `forget` | `(string $key, ...): bool` | Delete a specific key |
| `flush` | `(): bool` | Flush all Modularous cache entries |

### Invalidation

| Method | Signature | Description |
|--------|-----------|-------------|
| `invalidateModule` | `(string $module): bool` | Invalidate all cache for a module |
| `invalidateModuleRoute` | `(string $module, string $routeName): bool` | Invalidate all cache for a route |
| `invalidateByRelatedModel` | `(string $modelClass, $id): bool` | Invalidate by a single related model |
| `invalidateByRelatedModels` | `(array $relations): int` | Invalidate by multiple related models |
| `invalidateByPattern` | `(string $pattern): int` | Invalidate by key pattern |
| `invalidateForModel` | `(Model $model): void` | Invalidate all cache entries for a model instance |
| `invalidateCountCaches` | `(string $module, string $routeName): void` | Invalidate count caches for a route |
| `invalidateIndexCaches` | `(string $module, string $routeName): void` | Invalidate index caches for a route |

### Stats

| Method | Signature | Description |
|--------|-----------|-------------|
| `getStats` | `(?string $module = null): array` | Returns cache statistics |

## Usage

```php
use Unusualify\Modularity\Facades\ModularityCache;

$items = ModularityCache::remember(
    ModularityCache::generateCacheKey('Blog', 'posts', 'index'),
    ModularityCache::getTtl('index', 'Blog'),
    fn() => $repository->getIndexItems(),
    'Blog',
    'posts'
);

// Invalidate after a save
ModularityCache::invalidateModuleRoute('Blog', 'posts');
```
