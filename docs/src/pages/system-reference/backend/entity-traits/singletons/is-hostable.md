---
sidebarPos: 1
sidebarTitle: IsHostable
---

# IsHostable

**Namespace**: `Unusualify\Modularity\Entities\Traits\IsHostable`

Extends `HasSlug` and `Core\ModelHelpers` to support multi-level hostable slug routing. Traverses `BelongsTo` and `HasMany` relationship chains to build full slug paths including all ancestor segments. Useful for nested page hierarchies where each level has its own slug.

---

## Dependencies

Automatically composes:
- `HasSlug` — slug storage and route binding
- `Core\ModelHelpers` — `definedRelations` introspection

---

## Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$hostableColumn` | `string` | `'url'` | Column name that stores the hostable URL segment |

---

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `hostables` | `(): Collection` (static) | Returns all hostable, published models (applies `hostable` + `published` scopes) |
| `getHostableColumn` | `(): string` | Returns the column name for the hostable URL |
| `hostableRouteArguments` | `(): array` | Builds the full route parameter array by traversing parent `BelongsTo` relationships — e.g. `['parent' => 'about-us', 'page' => 'team']` |
| `hostableParents` | `(): array` | Returns parent model instances that also use `IsHostable` |
| `hostableParentRecords` | `(): array` | Returns the actual parent model records |
| `hostableChilds` | `(): array` | Returns child model classes that use `IsHostable` via `HasMany` |
| `hostableChildRouteParameters` | `(): array` | Returns child route parameter placeholders |
| `hostableRouteBindingParameter` | `(): string` (static) | Returns the route parameter placeholder for this model (e.g. `{page}`) |

---

## Scopes

| Scope | Description |
|-------|-------------|
| `scopeHostable($query)` | Records with a non-null hostable column and no parent hostable (top-level) |
| `scopeNotParentHostable($query)` | Records where the parent model has no hostable URL |

---

## Usage

```php
use Unusualify\Modularity\Entities\Traits\IsHostable;

class Page extends Model
{
    use IsHostable;

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(Page::class, 'parent_id');
    }
}

// Route generation — builds full slug path
$page->hostableRouteArguments();
// ['parent' => 'about-us', 'page' => 'team']

// Query
Page::hostable()->get();
Page::notParentHostable()->get();

// All published hostable pages
Page::hostables();
```

::: tip Route binding
Because `IsHostable` composes `HasSlug`, route model binding works by slug automatically. Define your routes with the model's route parameter placeholder:
```php
Route::get('/{page}', PageController::class);
// or for nested:
Route::get('/{parent}/{page}', PageController::class);
```
:::
