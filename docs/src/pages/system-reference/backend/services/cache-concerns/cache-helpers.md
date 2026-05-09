---
sidebarPos: 2
sidebarTitle: CacheHelpers
---

# CacheHelpers

**File**: `src/Services/Concerns/CacheHelpers.php`  
**Uses**: `CacheTags`, `CacheInvalidation`

`CacheHelpers` is the primary trait consumed by `ModularousCacheService`. It wraps standard Laravel cache operations (`remember`, `get`, `put`, `forget`, `flush`) with tag awareness — automatically applying the correct tag set when the cache store supports tags, and falling back to untagged operations otherwise.

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `remember` | `remember(string $key, int $ttl, Closure $cb, ?string $module, ?string $route)` | Tag-aware `Cache::remember()` |
| `rememberForever` | `rememberForever(string $key, Closure $cb, ?string $module, ?string $route)` | Tag-aware `Cache::rememberForever()` |
| `rememberWithRelations` | `rememberWithRelations(string $key, int $ttl, Closure $cb, ?string $module, ?string $route, array $relations)` | `remember` + adds relation tags for granular per-record invalidation |
| `get` | `get(string $key, $default, ?string $module, ?string $route)` | Tag-aware `Cache::get()` |
| `put` | `put(string $key, $value, int $ttl, ?string $module, ?string $route): bool` | Tag-aware `Cache::put()` |
| `putWithRelations` | `putWithRelations(string $key, $value, int $ttl, ?string $module, ?string $route, array $relations): bool` | `put` + relation tags |
| `has` | `has(string $key, ?string $module, ?string $route): bool` | Tag-aware existence check |
| `forget` | `forget(string $key, ?string $module, ?string $route): bool` | Tag-aware key deletion (uses `onlyRoute: true` tag for narrow scope) |
| `flush` | `flush(): bool` | Flush all modularous caches (by global prefix tag, or pattern fallback) |

## Tag Selection Logic

Each method applies tags as follows when the store supports them and `$module` is provided:

| `$module` | `$route` | Tags used |
|-----------|----------|-----------|
| set | set | `getModuleRouteTags($module, $route)` |
| set | `null` | `getModuleTags($module)` |
| `null` | — | No tags (plain store call) |

## `isEnabled` Guard

Every method checks `isEnabled($module, $route)` first. If caching is disabled for the given scope, `remember` / `rememberForever` / `rememberWithRelations` call the callback directly and return its value; `get` returns `$default`; `put` / `putWithRelations` return `false`.

## Example

```php
// In a repository
$result = $cacheService->remember(
    key: $cacheKey,
    ttl: 3600,
    callback: fn() => $this->model->with('tags')->paginate(),
    moduleName: 'Orders',
    moduleRouteName: 'order'
);

// With relation tags for granular invalidation
$result = $cacheService->rememberWithRelations(
    key: $cacheKey,
    ttl: 3600,
    callback: fn() => Order::with('company')->find($id),
    moduleName: 'Orders',
    moduleRouteName: 'order',
    relations: ['Company' => $order->company_id]
);
```
