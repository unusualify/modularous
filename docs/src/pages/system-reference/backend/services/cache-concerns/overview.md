---
sidebarPos: 2
sidebarTitle: Overview
---

# Cache Concerns

**Directory**: `src/Services/Concerns/`

The Cache Concerns are three PHP traits that compose the caching behaviour used by `ModularousCacheService`. They separate the cache layer into distinct responsibilities:

| Trait | File | Purpose | Page |
|-------|------|---------|------|
| [CacheTags](/system-reference/backend/services/cache-concerns/cache-tags) | `Concerns/CacheTags.php` | Generates tag name arrays for module, route, type, and relation scopes | [→](/system-reference/backend/services/cache-concerns/cache-tags) |
| [CacheHelpers](/system-reference/backend/services/cache-concerns/cache-helpers) | `Concerns/CacheHelpers.php` | `remember`, `get`, `put`, `forget`, `flush` — all tag-aware | [→](/system-reference/backend/services/cache-concerns/cache-helpers) |
| [CacheInvalidation](/system-reference/backend/services/cache-concerns/cache-invalidation) | `Concerns/CacheInvalidation.php` | Invalidation by module, route, model, pattern, or relation tag | [→](/system-reference/backend/services/cache-concerns/cache-invalidation) |

## Composition

```
ModularousCacheService
  └── uses CacheHelpers
        └── uses CacheTags
        └── uses CacheInvalidation
              └── uses CacheTags
              └── uses WarmupCache
              └── uses ModularModel
```

`CacheHelpers` is the entry point — it composes `CacheTags` and `CacheInvalidation` into a single unified interface. `ModularousCacheService` only needs to `use CacheHelpers` to get all three.

## Required Interface

Classes using these traits must implement four abstract methods:

```php
protected function getStore(): Repository;    // cache store instance
protected function getPrefix(): string;       // cache key prefix
protected function usesTags(): bool;          // tag support detection
protected function isEnabled(
    ?string $moduleName = null,
    ?string $moduleRouteName = null,
    ?string $type = null
): bool;                                       // feature-flag check
```
