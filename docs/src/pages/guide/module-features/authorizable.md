---
outline: deep
sidebarPos: 9
---

# Authorizable

The Authorizable feature assigns an authorized user (e.g. owner, responsible person) to a record via a morphOne Authorization model. It follows the triple pattern: Entity trait + Repository trait + Hydrate.

## Entity Trait: HasAuthorizable

Add the `HasAuthorizable` trait to your model:

```php
<?php

namespace Modules\Ticket\Entities;

use Unusualify\Modularity\Entities\Model;
use Unusualify\Modularity\Entities\Traits\HasAuthorizable;

class Ticket extends Model
{
    use HasAuthorizable;

    protected static $defaultAuthorizedModel = \Modules\SystemUser\Entities\User::class;
}
```

### Relationships

- **authorizationRecord()** — morphOne to `Authorization`
- **authorizedUser()** — hasOneThrough to the authorized model (e.g. User)

### Accessors and Scopes

- **is_authorized** — appended; true if an authorized user exists
- **scopeHasAuthorization** — records authorized for the given user
- **scopeIsAuthorizedToYou** — records authorized to the current user
- **scopeIsAuthorizedToYourRole** — records authorized to users with the current user's role
- **scopeHasAnyAuthorization** — records with any authorization
- **scopeUnauthorized** — records without authorization

### Boot Logic

- On **retrieved**: Populates `authorized_id` and `authorized_type` from the authorization record
- On **saving**: Stores authorization fields for after-save sync
- On **saved**: Creates or updates the authorization record
- On **deleting**: Deletes the authorization record

## Repository Trait: AuthorizableTrait

Add `AuthorizableTrait` to your repository:

```php
<?php

namespace Modules\Ticket\Repositories;

use Unusualify\Modularity\Repositories\Repository;
use Unusualify\Modularity\Repositories\Traits\AuthorizableTrait;

class TicketRepository extends Repository
{
    use AuthorizableTrait;

    public function __construct(Ticket $model)
    {
        $this->model = $model;
    }
}
```

### Methods

- **getTableFiltersAuthorizableTrait** — Returns table filters: authorized, unauthorized, your-authorizations (when user has authorization usage)

## Input Config

Add an authorize input to your route in `Config/config.php`:

```php
'routes' => [
    'item' => [
        'inputs' => [
            [
                'type' => 'authorize',
                'label' => 'Authorize',
                'authorized_type' => \Modules\SystemUser\Entities\User::class,  // optional; inferred from model
                'scopeRole' => ['admin', 'manager'],  // optional; filter by Spatie role
            ],
        ],
    ],
],
```

## Hydrate: AuthorizeHydrate

`AuthorizeHydrate` transforms the input into a `select` schema.

### Requirements

| Key | Default |
|-----|---------|
| itemValue | id |
| itemTitle | name |
| label | Authorize |

### Output

- **type**: `select`
- **name**: `authorized_id`
- **multiple**: false
- **returnObject**: false
- **items**: Fetched from the authorized model (filtered by `scopeRole` if set)
- **noRecords**: true

### Authorized Model Resolution

The hydrate resolves `authorized_type` from:
1. Explicit `authorized_type` in input
2. `_module` + `_route` context
3. `routeName` in input

If the route's model uses `HasAuthorizable`, the hydrate uses `getAuthorizedModel()` to determine the authorized model class.
