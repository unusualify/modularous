---
sidebarPos: 4
sidebarTitle: CountBuilders
---

# CountBuilders

**Namespace**: `Unusualify\Modularous\Repositories\Logic\CountBuilders`

Provides cached aggregate count queries for the standard record status tabs (all, published, draft, trash) and a generic status-by-method helper. Composes `MethodTransformers`.

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `getCountForAll` | `(): int` | Counts all records (with current `$countScope` filters applied) |
| `getCountForPublished` | `(): int` | Counts records matching the `published()` scope |
| `getCountForDraft` | `(): int` | Counts records matching the `draft()` scope |
| `getCountForTrash` | `(): int` | Counts soft-deleted records (`onlyTrashed()`) |
| `getCountFor` | `(string $method, array $args): int` | Counts records for any named Eloquent scope (`scope{Method}` must exist on the model). Throws if the scope is not found. |

All methods use `cacheableCount()` (from `CacheableTrait`) for consistent cache key generation and TTL management. The active `$countScope` (set by `getCountByStatusSlug()`) is included in the cache key.

## Model Interface

For more efficient count queries, the model may define `newCountQuery()` — a query builder that excludes heavy joins or eager loads present in the standard `newQuery()`. If absent, `newQuery()` is used.

## Usage

```php
// Standard status tab counts
$all       = $repo->getCountForAll();
$published = $repo->getCountForPublished();
$draft     = $repo->getCountForDraft();
$trashed   = $repo->getCountForTrash();

// Custom scope count
$active  = $repo->getCountFor('active');
$premium = $repo->getCountFor('byPlan', ['premium']);
```
