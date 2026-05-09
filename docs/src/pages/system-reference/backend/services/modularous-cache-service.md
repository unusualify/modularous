---
sidebarPos: 18
sidebarTitle: ModularousCacheService
---

# ModularousCacheService

**File**: `src/Services/ModularousCacheService.php`  
**Facade**: `Unusualify\Modularous\Facades\ModularousCache`

A tag-aware cache service that wraps Laravel's cache system with module-scoped key generation, per-module/route/type TTL configuration, and automatic Redis/Memcached connection validation at boot time.

## Configuration

All options live under `config/modularous.php` → `cache`:

```php
'cache' => [
    'enabled'     => true,
    'driver'      => 'redis',       // redis | memcached | file | array
    'prefix'      => 'modularous',
    'use_tags'    => true,          // disable if your driver doesn't support tags
    'all_modules' => false,         // set to true to cache all modules by default

    // Global TTLs (seconds)
    'ttl' => [
        'index'   => 300,
        'show'    => 600,
        'options' => 300,
    ],

    // Per-module overrides
    'modules' => [
        'Product' => [
            'enabled' => true,
            'ttl'     => ['index' => 60, 'show' => 120],
        ],
        'Product.product' => [          // per-route key: ModuleName.routeName
            'enabled' => true,
            'ttl'     => ['index' => 30],
        ],
    ],
],
```

## Cache Key Format

Generated keys follow this pattern:

```
{prefix}:{ModuleName}:{RouteName}:{type}:{params_hash}
```

**Example**: `modularous:Product:Product:index:d41d8cd9`

- `prefix` — from `cache.prefix` (default `modularous`)
- `ModuleName` / `RouteName` — converted to StudlyCase
- `type` — `index`, `show`, `options`, or any custom string
- `params_hash` — MD5 of sorted, serialized query parameters; `default` if no params

## Key Methods

### Checking & Configuration

| Method | Signature | Description |
|--------|-----------|-------------|
| `isEnabled` | `isEnabled(?string $module, ?string $route, ?string $type): bool` | Check whether caching is active for a given context. Returns `false` if driver is disconnected. |
| `usesTags` | `usesTags(): bool` | Returns `true` when tags are configured and the driver supports them |
| `getDriver` | `getDriver(): string` | Return the configured driver name |
| `getPrefix` | `getPrefix(): string` | Return the cache key prefix |
| `getConfig` | `getConfig(): array` | Return the full cache config array |

### Key Generation

| Method | Signature | Description |
|--------|-----------|-------------|
| `generateCacheKey` | `generateCacheKey(string $module, string $route, string $type, array $params): string` | Build a deterministic cache key for the given context |

### TTL Resolution

| Method | Signature | Description |
|--------|-----------|-------------|
| `getTtl` | `getTtl(string $type, ?string $module, ?string $route): int` | Resolve the effective TTL, checking route-level → module-level → global in that order |

### Diagnostics

| Method | Signature | Description |
|--------|-----------|-------------|
| `getStats` | `getStats(?string $module): array` | Return `keys_count`, `keys[]`, and `using_tags` for a module (Redis only) |
| `getStore` | `getStore(): Repository` | Return the underlying `Illuminate\Cache\Repository` instance |

## Tag Behaviour

On boot, the service runs a small verification test to confirm that cache tags actually work (write → read → flush → confirm flush). If the flush does not clear the value, `use_tags` is automatically disabled in memory even if it is `true` in config. This prevents silent cache invalidation failures with misconfigured Redis instances.

Supported tag drivers: Redis (via `predis` or `phpredis`), Memcached.  
Unsupported: `file`, `database`, `array` — tags are silently disabled for these.

## Repository Integration

`CacheableTrait` in `src/Repositories/Logic/` calls these methods automatically on every `index()` and `show()` repository call when the module has caching enabled:

```php
// Simplified internal flow in CacheableTrait
if ($this->cacheService->isEnabled($moduleName, $routeName, 'index')) {
    $key = $this->cacheService->generateCacheKey($moduleName, $routeName, 'index', $params);
    $ttl = $this->cacheService->getTtl('index', $moduleName, $routeName);

    return Cache::remember($key, $ttl, fn () => $this->fetchIndex($params));
}
```

## Artisan Commands

The cache system has six dedicated commands:

| Command | Description |
|---------|-------------|
| [`modularous:cache:clear`](/guide/console/cache/cache-clear) | Clear all Modularous cache entries |
| [`modularous:cache:list`](/guide/console/cache/cache-list) | List cached keys and their TTLs |
| [`modularous:cache:warm`](/guide/console/cache/cache-warm) | Pre-warm the cache for all enabled modules |
| [`modularous:cache:stats`](/guide/console/cache/cache-stats) | Show cache statistics per module |
| [`modularous:cache:versions`](/guide/console/cache/cache-versions) | Show cache version history |
| [`modularous:cache:graph`](/guide/console/cache/cache-graph) | Visualize the cache relationship graph |
