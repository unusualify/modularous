---
sidebarPos: 4
sidebarTitle: HasRelation
---

# Secondary\HasRelation

**Namespace**: `Unusualify\Modularous\Entities\Traits\Secondary\HasRelation`

Minimal stub trait that registers a `forceDeleting` boot hook. Use it as a base for models that need cleanup logic on force-delete without committing to the full `HasRelated` pivot system.

---

## Boot Behavior

| Event | Action |
|-------|--------|
| `forceDeleting` | Hook fires before a force-delete — empty by default, intended to be overridden |

---

## Usage

The trait provides the hook point; add your own cleanup by calling `static::forceDeleting()` in your model's boot method after including the trait:

```php
use Unusualify\Modularous\Entities\Traits\Secondary\HasRelation;

class Article extends Model
{
    use HasRelation;

    protected static function boot(): void
    {
        parent::boot();

        static::forceDeleting(function (self $model) {
            // custom cleanup before force-delete
            $model->someRelation()->forceDelete();
        });
    }
}
```
