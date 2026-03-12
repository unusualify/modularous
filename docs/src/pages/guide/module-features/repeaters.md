---
outline: deep
sidebarPos: 12
---

# Repeaters

The Repeaters feature adds repeatable blocks (e.g. FAQs, team members) with nested inputs. It follows the triple pattern: Entity trait + Repository trait + Hydrate.

## Entity Trait: HasRepeaters

Add the `HasRepeaters` trait to your model:

```php
<?php

namespace Modules\Page\Entities;

use Unusualify\Modularity\Entities\Model;
use Unusualify\Modularity\Entities\Traits\HasRepeaters;

class Page extends Model
{
    use HasRepeaters;
}
```

### Relationships

- **repeaters($role, $locale)** — morphMany to Repeater; optionally filtered by role and locale

### Dependencies

HasRepeaters uses HasFiles, HasImages, HasPriceable, HasFileponds for nested media and pricing in repeater blocks.

## Repository Trait: RepeatersTrait

Add `RepeatersTrait` to your repository:

```php
<?php

namespace Modules\Page\Repositories;

use Unusualify\Modularity\Repositories\Repository;
use Unusualify\Modularity\Repositories\Traits\RepeatersTrait;

class PageRepository extends Repository
{
    use RepeatersTrait;

    public function __construct(Page $model)
    {
        $this->model = $model;
    }
}
```

### Methods

- **setColumnsRepeatersTrait** — Collects repeater columns from route inputs
- **hydrateRepeatersTrait** — Hydrates repeater data
- **afterSaveRepeatersTrait** — Persists repeater blocks
- **getFormFieldsRepeatersTrait** — Populates form fields from repeaters

## Input Config

Add a repeater input to your route in `Config/config.php`:

```php
'routes' => [
    'item' => [
        'inputs' => [
            [
                'type' => 'repeater',
                'name' => 'faqs',
                'label' => 'FAQs',
                'draggable' => true,
                'orderKey' => 'position',
                'schema' => [
                    ['name' => 'question', 'type' => 'text', 'label' => 'Question'],
                    ['name' => 'answer', 'type' => 'textarea', 'label' => 'Answer'],
                ],
            ],
        ],
    ],
],
```

### Schema

The `schema` array defines nested inputs. Each item can use any input type (text, textarea, select, image, file, etc.). Use `translated` for locale-specific fields.

## Hydrate: RepeaterHydrate

`RepeaterHydrate` transforms the input into `input-repeater` schema.

### Requirements

| Key | Default |
|-----|---------|
| autoIdGenerator | true |
| itemValue | id |
| itemTitle | name |

### Output

- **type**: `input-repeater`
- **root**: `default` (for type `repeater`) or the original type name
- **orderKey**: Set when `draggable` is true (default: `position`)
- **singularLabel**: Singular form of label
- **schema**: Nested inputs; `translated` defaults to false per item

### JsonRepeaterHydrate

For JSON-stored repeaters (no Repeater model), use `type: 'json-repeater'` instead of `repeater`.
