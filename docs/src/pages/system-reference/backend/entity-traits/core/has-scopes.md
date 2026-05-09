---
sidebarPos: 5
sidebarTitle: HasScopes
---

# Core\HasScopes

**Namespace**: `Unusualify\Modularous\Entities\Traits\Core\HasScopes`

Provides the standard visibility scopes used across all Modularous models. Integrates with `Traitify` for conditional global scope registration. Also provides `handleScopes` for applying an array of named scopes to a query.

---

## Boot Behavior

`bootHasScopes()` calls `setFeatureGlobalScopes()`, which discovers and registers all static `addGlobalScopesHasX()` methods from every trait composed into the model.

---

## Scopes

| Scope | Description |
|-------|-------------|
| `scopePublished($query)` | Records where `{table}.published = true` |
| `scopeDraft($query)` | Records where `{table}.published = false` |
| `scopeVisible($query)` | Records within `publish_start_date` and `publish_end_date` window (if fillable) |
| `scopePublishedInListings($query)` | Published + visible + `public = true` (if fillable) |
| `scopeBetween($query, $column, $start, $end)` | Records where `$column` falls in the date range |
| `scopeCreatedAtBetween($query, $start, $end)` | Created within the date range |
| `scopeUpdatedAtBetween($query, $start, $end)` | Updated within the date range |

---

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `hasScope` | `(string $scopeName): bool` (static) | Returns `true` if the scope exists as a method, macro, or named scope |
| `handleScopes` | `(Builder $query, array $scopes): Builder` (static) | Applies an associative array of scope names/values to the query; supports `LIKE` (`%column`), negation (`!value`), and array (`whereIn`) |
| `setFeatureGlobalScopes` | `(): void` (static) | Calls all `addGlobalScopesHasX()` static methods and registers their scopes |
| `getUncountableGlobalScopes` | `(): array` (static) | Returns scope names that should be excluded from `COUNT` queries |
| `newCountQuery` | `(): Builder` | Returns a query builder with uncountable scopes removed |

---

## `addGlobalScopes*` Convention

Traits that need to register global query scopes declare a static method named `addGlobalScopesHasX()` returning an array:

```php
public static function addGlobalScopesHasX(): array
{
    return [
        'my_scope_name' => [
            'scope' => function ($query) { $query->withExists('someRelation'); },
            'count' => false,  // exclude from COUNT queries
        ],
    ];
}
```

`setFeatureGlobalScopes()` (called from `bootHasScopes`) discovers and registers all such methods automatically.

---

## Usage

```php
use Unusualify\Modularous\Entities\Traits\Core\HasScopes;

class Article extends Model
{
    use HasScopes;
}

Article::published()->get();
Article::publishedInListings()->paginate(15);
Article::draft()->get();
Article::createdAtBetween('2024-01-01', '2024-12-31')->get();

// Dynamic scope application
$filters = ['published' => true, '%title' => 'laravel'];
Article::handleScopes(Article::query(), $filters)->get();
```
