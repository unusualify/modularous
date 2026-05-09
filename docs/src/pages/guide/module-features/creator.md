---
outline: deep
sidebarPos: 5
---

# Creator

The Creator feature tracks who created a record via a morphOne CreatorRecord. It follows the triple pattern: Entity trait + Repository trait + Hydrate.

## Entity Trait: HasCreator

Add the `HasCreator` trait to your model:

```php
<?php

namespace Modules\Ticket\Entities;

use Unusualify\Modularous\Entities\Model;
use Unusualify\Modularous\Entities\Traits\HasCreator;

class Ticket extends Model
{
    use HasCreator;

    protected static $defaultHasCreatorModel = \Modules\SystemUser\Entities\User::class;
}
```

### Relationships

- **creatorRecord()** — morphOne to `CreatorRecord`
- **creator()** — hasOneThrough to the creator model (e.g. User)
- **company()** — hasOne to Company via creator
- **creatorCompany()** — hasOne to Company via creator (relation)

### Scopes

- **scopeIsCreator** — records created by a given creator ID
- **scopeIsMyCreation** — records created by the current user
- **scopeHasAccessToCreation** — records the current user has access to (by role or company)

### Boot Logic

- On **saving**: Stores `custom_creator_id` for after-save sync
- On **saved**: Creates or updates the creator record (on create: uses custom_creator_id or Auth user)
- On **deleting**: Deletes the creator record

## Repository Trait: CreatorTrait

Add `CreatorTrait` to your repository:

```php
<?php

namespace Modules\Ticket\Repositories;

use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Repositories\Traits\CreatorTrait;

class TicketRepository extends Repository
{
    use CreatorTrait;

    public function __construct(Ticket $model)
    {
        $this->model = $model;
    }
}
```

### Methods

- **filterCreatorTrait** — Applies `hasAccessToCreation` scope
- **getFormFieldsCreatorTrait** — Populates `custom_creator_id` from the creator relation
- **prependFormSchemaCreatorTrait** — Prepends a creator input to the form schema

## Input Config

Add a creator input to your route in `Config/config.php`:

```php
'routes' => [
    'item' => [
        'inputs' => [
            [
                'type' => 'creator',
                'label' => 'Creator',
                'allowedRoles' => ['superadmin'],
                'with' => ['company'],
                'appends' => ['email_with_company'],
            ],
        ],
    ],
],
```

## Hydrate: CreatorHydrate

`CreatorHydrate` transforms the input into `input-browser` schema.

### Requirements

| Key | Default |
|-----|---------|
| label | Creator |
| itemTitle | email_with_company |
| appends | ['email_with_company'] |
| with | ['company'] |
| allowedRoles | ['superadmin'] |

### Output

- **type**: `input-browser`
- **name**: `custom_creator_id`
- **multiple**: false
- **itemValue**: id
- **returnObject**: false
- **endpoint**: `admin.system.user.index` with light, eager, appends params
