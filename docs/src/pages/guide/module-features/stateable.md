---
outline: deep
sidebarPos: 14
---

# Stateable

The Stateable feature adds workflow states (e.g. draft, published, archived) to a model via a morphOne Stateable pivot. It follows the triple pattern: Entity trait + Repository trait + Hydrate.

## Entity Trait: HasStateable

Add the `HasStateable` trait to your model:

```php
<?php

namespace Modules\Article\Entities;

use Unusualify\Modularous\Entities\Model;
use Unusualify\Modularous\Entities\Traits\HasStateable;

class Article extends Model
{
    use HasStateable;

    protected static $default_states = [
        ['code' => 'draft', 'icon' => 'pencil', 'color' => 'grey'],
        ['code' => 'published', 'icon' => 'check-circle', 'color' => 'success'],
        ['code' => 'archived', 'icon' => 'archive', 'color' => 'warning'],
    ];

    protected static $initial_state = 'draft';
}
```

### Relationships

- **stateable()** — morphOne to `Stateable` (pivot to State)
- **state()** — hasOneThrough to `State`

### Accessors and Methods

- **state** — Current state (hydrated with icon, color, name)
- **stateable_code** — Code of the current state
- **state_formatted** — HTML for display (icon + name)
- **states** — All default states for the model
- **getStates** — Returns default states
- **getDefaultStates** — Returns formatted default states
- **getInitialState** — Returns the initial state
- **hydrateState** — Applies config (icon, color, translations) to a State

### Boot Logic

- On **saving**: Handles `initial_stateable` and `stateable_id` updates
- On **created**: Creates Stateable record with initial state
- On **retrieved**: Sets `stateable_id` from the state relation
- On **saved**: Updates state when `stateable_id` changes; dispatches `StateableUpdated` event

## Repository Trait: StateableTrait

Add `StateableTrait` to your repository:

```php
<?php

namespace Modules\Article\Repositories;

use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Repositories\Traits\StateableTrait;

class ArticleRepository extends Repository
{
    use StateableTrait;

    public function __construct(Article $model)
    {
        $this->model = $model;
    }
}
```

### Methods

- **getStateableList** — Returns states for the select (id, name)
- **getTableFiltersStateableTrait** — Returns table filters per state
- **getStateableFilterList** — Returns filter list with counts
- **getCountByStatusSlugStateableTrait** — Count by state code

## Input Config

Add a stateable input to your route in `Config/config.php`:

```php
'routes' => [
    'item' => [
        'inputs' => [
            [
                'type' => 'stateable',
                'label' => 'Status',
                '_moduleName' => 'Article',
                '_routeName' => 'item',
            ],
        ],
    ],
],
```

### Module/Route Context

`_moduleName` and `_routeName` are required so the hydrate can resolve the repository and call `getStateableList()`.

## Hydrate: StateableHydrate

`StateableHydrate` transforms the input into a `select` schema.

### Requirements

| Key | Default |
|-----|---------|
| label | Status |

### Output

- **type**: `select`
- **name**: `stateable_id`
- **itemTitle**: name
- **itemValue**: id
- **items**: From repository `getStateableList(itemValue: 'name')`

### Exception

Throws if `_moduleName` or `_routeName` is missing, since the hydrate needs the route's repository to fetch states.
