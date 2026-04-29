---
outline: deep
sidebarPos: 13
---

# Spreadable

The Spreadable feature stores flexible JSON data in a Spread model, allowing dynamic attributes beyond the table columns. It follows the triple pattern: Entity trait + Repository trait + Hydrate.

## Entity Trait: HasSpreadable

Add the `HasSpreadable` trait to your model:

```php
<?php

namespace Modules\Page\Entities;

use Unusualify\Modularity\Entities\Model;
use Unusualify\Modularity\Entities\Traits\HasSpreadable;

class Page extends Model
{
    use HasSpreadable;

    protected static $spreadableSavingKey = 'spread_payload';
}
```

### Relationships

- **spreadable()** — morphOne to `Spread`

### Methods

- **getSpreadableSavingKey** — Returns the key for spread data (default: `spread_payload`)
- **getReservedKeys** — Returns keys that cannot be used as spread attributes (table columns, relations, etc.)
- **getSpreadableKeys** — Returns keys currently in the spread

### Boot Logic

- On **saving**: Persists spread data to the Spread model; unsets the spread key from attributes
- On **created**: Creates the Spread record
- On **retrieved**: Loads spread content as dynamic attributes

## Repository Trait: SpreadableTrait

Add `SpreadableTrait` to your repository:

```php
<?php

namespace Modules\Page\Repositories;

use Unusualify\Modularity\Repositories\Repository;
use Unusualify\Modularity\Repositories\Traits\SpreadableTrait;

class PageRepository extends Repository
{
    use SpreadableTrait;

    public function __construct(Page $model)
    {
        $this->model = $model;
    }
}
```

### Methods

- **setColumnsSpreadableTrait** — Collects spread input columns
- **beforeSaveSpreadableTrait** — Merges spread fields before save
- **prepareFieldsBeforeSaveSpreadableTrait** — Moves spreadable fields into the spread key
- **getFormFieldsSpreadableTrait** — Populates form from spread content

## Input Config

Add a spread input and mark spreadable fields in your route in `Config/config.php`:

```php
'routes' => [
    'item' => [
        'inputs' => [
            [
                'type' => 'spread',
                '_moduleName' => 'Page',
                '_routeName' => 'item',
            ],
            [
                'name' => 'meta_description',
                'type' => 'text',
                'label' => 'Meta Description',
                'spreadable' => true,
            ],
            [
                'name' => 'og_image',
                'type' => 'image',
                'label' => 'OG Image',
                'spreadable' => true,
            ],
        ],
    ],
],
```

### spreadable Flag

Inputs with `spreadable => true` are stored in the Spread JSON instead of table columns. Their names are added to `reservedKeys` so they are not overwritten.

## Hydrate: SpreadHydrate

`SpreadHydrate` transforms the input into `input-spread` schema.

### Output

- **type**: `input-spread`
- **name**: From `getSpreadableSavingKey()` when `_moduleName` and `_routeName` are set; otherwise `spread_payload`
- **reservedKeys**: From model `getReservedKeys()` plus inputs with `spreadable => true`
- **col**: Full width (12 cols)

### Module/Route Context

When `_moduleName` and `_routeName` are provided, the hydrate resolves the model and uses `getReservedKeys()` and `getRouteInputs()` to build `reservedKeys` and the spread name.
