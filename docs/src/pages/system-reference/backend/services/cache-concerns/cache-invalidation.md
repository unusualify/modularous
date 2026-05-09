---
sidebarPos: 3
sidebarTitle: CacheInvalidation
---

# CacheInvalidation

**File**: `src/Services/Concerns/CacheInvalidation.php`  
**Uses**: `CacheTags`, `ModularModel`, `WarmupCache`

`CacheInvalidation` provides all cache-clearing operations. Methods range from coarse (flush an entire module) to fine-grained (flush caches tagged with a specific model record).

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `invalidateModule` | `invalidateModule(string $moduleName): bool` | Flush all caches for a module (all routes) |
| `invalidateModuleRoute` | `invalidateModuleRoute(string $module, string $route): bool` | Flush all caches for one route within a module |
| `invalidateByRelatedModel` | `invalidateByRelatedModel(string $modelClass, $id): bool` | Flush only caches tagged with `rel:{Model}:{id}` |
| `invalidateByRelatedModels` | `invalidateByRelatedModels(array $relations): int` | Bulk version; returns count of tags flushed |
| `invalidateByPattern` | `invalidateByPattern(string $pattern): int` | Redis SCAN pattern delete (no-tags fallback only) |
| `invalidateCountCaches` | `invalidateCountCaches(string $module, string $route, bool $onlyRoute): void` | Flush count-type caches for a route |
| `invalidateIndexCaches` | `invalidateIndexCaches(string $module, string $route, bool $onlyRoute): void` | Flush index/listing caches for a route |
| `invalidateFormattedItemCache` | `invalidateFormattedItemCache(string $module, string $route, $id): void` | Flush formatted-item cache for one record |
| `invalidateFormItemCache` | `invalidateFormItemCache(string $module, string $route, $id): void` | Flush form-item cache for one record |
| `invalidateForModel` | `invalidateForModel(Model $model, array $types, array $options): void` | Full model invalidation — flushes all relevant cache types and optionally warms up new caches |

## `invalidateForModel` Detail

This is the primary entry point called from model observers. It:

1. Resolves `$moduleName` and `$moduleRouteName` from the model via `getModuleNameFromModel()` / `getModuleRouteNameFromModel()`.
2. With tags: flushes the route tag entirely.
3. Without tags: selectively invalidates `counts`, `index`, `formattedItem`, `formItem` by pattern, only if each type is enabled and the model is not newly created.
4. Optionally calls `warmupByModel($model)` after invalidation (controlled by `$options['warmup']`, default `true`).

## Tag vs Pattern Behaviour

| Store type | Invalidation |
|------------|-------------|
| Redis / Memcached (tags supported) | `Cache::tags([...])->flush()` — fast, atomic |
| File / database (no tags) | `invalidateByPattern()` via Redis SCAN — only works when the cache driver is Redis without tags |

> `invalidateByPattern()` logs a warning if called while tags are enabled, because tagged cache keys have a hashed namespace prefix that makes pattern matching impossible.

## Example

```php
// After saving an Order model
app(ModularousCacheService::class)->invalidateForModel($order);

// Selectively — only invalidate index and count caches
app(ModularousCacheService::class)->invalidateForModel($order, [
    'index'  => true,
    'counts' => true,
    'formattedItem' => false,
    'formItem'      => false,
], ['warmup' => false]);

// Granular — flush only caches that referenced Company:5
app(ModularousCacheService::class)->invalidateByRelatedModel(Company::class, 5);
```
