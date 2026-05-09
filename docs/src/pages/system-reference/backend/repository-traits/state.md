---
sidebarPos: 8
sidebarTitle: State Traits
---

# State Repository Traits

This trait provides the repository-level logic for the state machine feature. It pairs with the [`HasStateable`](../entity-traits/model-behavior/overview#hasstateable) entity trait.

---

## StateableTrait

**Namespace**: `Unusualify\Modularous\Repositories\Traits\StateableTrait`

Builds state-based filter lists and data table filter tabs by querying the model's configured states. Provides count-by-status methods for the table UI.

### Lifecycle Hooks

| Hook | Description |
|------|-------------|
| `getTableFiltersStateableTrait` | Returns an array of filter tab definitions, one per state defined in the model's `$default_states` |

### Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `getStateableFilterList` | `(): array` | Builds a list of `{name, code, slug, number}` for each configured state. Applies repository filters, counts matching records, and excludes states with zero results. |
| `getTableFiltersStateableTrait` | `(mixed $scope): array` | Returns table filter tab definitions with `{name, code, slug, methods, params}` for the data table component. Supports role-based visibility via `$stateableFilterUserRoles`. |
| `getCountByStatusSlugStateableTrait` | `(string $slug, array $scope): int\|false` | Returns the count of records matching a given state code. Returns `false` if the slug is not a valid state code. |
| `getStateableList` | `(string $itemValue = 'name'): array` | Returns `[{id, name}]` pairs for all configured states, ordered by the model's `$default_states` definition order. |

### Filter List Structure

`getStateableFilterList()` returns an array where each entry is:

```php
[
    'name'   => 'Published',          // translated state name
    'code'   => 'published',          // state code
    'slug'   => 'isStateablePublished', // scope name for filtering
    'number' => 42,                   // record count
]
```

### Table Filter Structure

`getTableFiltersStateableTrait()` returns filter definitions:

```php
[
    'name'    => 'Published',
    'code'    => 'published',
    'slug'    => 'isStateablePublished',
    'methods' => 'getCountByStatusSlug',
    'params'  => ['published', $scope],
    // optional: 'allowedRoles' => ['admin', 'manager']
]
```

### Role-Based Filter Visibility

Define `$stateableFilterUserRoles` as a static property on your repository to restrict which roles can see specific state filters:

```php
class OrderRepository extends Repository
{
    use StateableTrait;

    protected static $stateableFilterUserRoles = ['admin', 'manager'];
}
```

### Usage

```php
use Unusualify\Modularous\Repositories\Traits\StateableTrait;

class OrderRepository extends Repository
{
    use StateableTrait;
}

// Get filter list for state-based navigation
$filters = $repo->getStateableFilterList();
// [
//     ['name' => 'Draft', 'code' => 'draft', 'slug' => 'isStateableDraft', 'number' => 5],
//     ['name' => 'Published', 'code' => 'published', 'slug' => 'isStateablePublished', 'number' => 12],
// ]

// Get state options for dropdowns
$states = $repo->getStateableList();
// [['id' => 1, 'name' => 'Draft'], ['id' => 2, 'name' => 'Published']]

// Count records by state
$count = $repo->getCountByStatusSlugStateableTrait('published');
```
