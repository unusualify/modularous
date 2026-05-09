---
sidebarPos: 3
sidebarTitle: HasCaching
---

# Core\HasCaching

**Namespace**: `Unusualify\Modularous\Entities\Traits\Core\HasCaching`

Wires up automatic cache invalidation via `CacheObserver` on model events (`created`, `updated`, `deleted`, `restored`, `forceDeleted`). Composites `Cacheable` for the underlying cache key/store management.

---

## Boot Behavior

`bootHasCaching()` registers `CacheObserver` on the model. The observer fires cache invalidation on every mutating event.

---

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `shouldCacheInvalidate` | `(): bool` | Override to conditionally suppress cache invalidation (default: `true`) |
| `withoutCacheInvalidation` | `(): static` | Sets `$skipCacheInvalidation = true` on the instance; returns `$this` for chaining |
| `withCacheInvalidation` | `(): static` | Re-enables cache invalidation on the instance; returns `$this` for chaining |

---

## Usage

```php
use Unusualify\Modularous\Entities\Traits\Core\HasCaching;

class Article extends Model
{
    use HasCaching;
}

// Bulk update without triggering cache invalidation on each row
Article::all()->each(fn ($a) => $a->withoutCacheInvalidation()->update(['views' => 0]));

// Conditionally disable based on context
class DraftArticle extends Article
{
    public function shouldCacheInvalidate(): bool
    {
        return $this->published; // only invalidate when published
    }
}
```

::: tip Combine with HasCacheDependents
Add `HasCacheDependents` to also invalidate caches of related models when this model changes:
```php
class Company extends Model
{
    use HasCaching, HasCacheDependents;

    protected array $cacheDependents = ['press_release'];
}
```
:::
