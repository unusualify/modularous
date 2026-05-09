---
sidebarPos: 2
sidebarTitle: CacheableTrait
---

# CacheableTrait

**Namespace**: `Unusualify\Modularous\Repositories\Logic\CacheableTrait`

Adds relationship-aware caching to repository queries. Composes the `Cacheable` and `HasUserAwareCache` traits to provide TTL-based caching with automatic tagging by related model IDs for granular invalidation.

## Composed Traits

```php
trait CacheableTrait
{
    use Cacheable, HasUserAwareCache;
}
```

## Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$trackCacheRelations` | `bool` | `true` | Whether to extract foreign key IDs from results and tag caches with them |

## Configuration Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `withRelationTracking` | `(bool $enabled = true): static` | Enable or disable relationship tracking (fluent) |
| `withoutRelationTracking` | `(): static` | Shorthand to disable relationship tracking |

## Core Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `cacheableCount` | `(string $slug, callable $callback, array $additionalParams): int` | Generic cached count with consistent key generation. Used for count-based filters (all, published, draft, etc.) |
| `getByIdCached` | `($id, $with, $withCount, $lazy, $scopes, $useDefaultScopes): Model` | Cached version of `getById()`. Supports user-aware cache keys when scopes are present. Falls back to uncached `getById()` when caching is disabled. |

## Relationship Extraction

| Method | Signature | Description |
|--------|-----------|-------------|
| `extractRelationIds` | `(Model $model): array` | Scans model attributes for `*_id` foreign keys, resolves them to related model classes via relationship methods, returns `['ModelClass' => id]` |
| `extractRelationIdsFromCollection` | `(Collection $collection): array` | Aggregates `extractRelationIds()` across a collection, returns `['ModelClass' => [id1, id2, ...]]` |

## Cache-with-Relations Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `rememberIndexWithRelations` | `(string $cacheKey, int $ttl, ...)` | Fetches index data, extracts relation IDs from all items (handles both paginated and collection results), caches with relation tags via `ModularousCache::putWithRelations()` |
| `rememberRecordWithRelations` | `(string $cacheKey, int $ttl, ...)` | Fetches a single record, extracts relation IDs, caches with relation tags |

## Cache Key Strategy

For `getByIdCached()`:

- **Simple queries** (no extra with/scopes) → `generateTypeCacheKey('record', ['id' => $id])`
- **Complex queries** → `ModularousCache::generateCacheKey(module, route, 'record', $params)`
- **User-aware queries** (scopes present + user-aware cache enabled) → adds user context to params

## How Relation Tracking Works

```
1. Query executes (cache miss)
2. extractRelationIds() scans result for *_id attributes
   └─ e.g., company_id=5 → ['App\Models\Company' => 5]
3. ModularousCache::putWithRelations(key, data, ttl, module, route, relations)
4. When Company #5 is updated → cache entries tagged with it are invalidated
```

## Usage

```php
// Relationship tracking is enabled by default on all repositories.

// Fetch a cached record
$item = $repo->getByIdCached($id, ['images', 'tags']);

// Temporarily disable relation tracking for performance
$items = $repo->withoutRelationTracking()->getByIdCached($id);

// Use cached counts
$count = $repo->cacheableCount('published', fn() => $this->model->published()->count());
```
