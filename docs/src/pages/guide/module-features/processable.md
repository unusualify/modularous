---
outline: deep
sidebarPos: 9
---

# Processable

The Processable feature adds a process lifecycle (e.g. preparing, in progress, completed) with status history. It follows the triple pattern: Entity trait + Repository trait + Hydrate.

## Entity Trait: Processable

Add the `Processable` trait to your model:

```php
<?php

namespace Modules\Order\Entities;

use Unusualify\Modularous\Entities\Model;
use Unusualify\Modularous\Entities\Traits\Processable;

class Order extends Model
{
    use Processable;
}
```

### Relationships

- **process()** — morphOne to `Process`
- **processHistories()** — hasManyThrough to `ProcessHistory` via Process
- **processHistory()** — hasOneThrough to the latest ProcessHistory

### Methods

- **setProcessStatus($status, $reason = null)** — Updates process status and creates a history record

### Accessors

- **has_process_history** — Whether the model has process history
- **processable_status** — Current status (when set on save)
- **processable_reason** — Reason for status change

### Boot Logic

- On **created**: Creates a Process with status `PREPARING`
- On **saved**: If `processable_status` is set, calls `setProcessStatus`

### ProcessStatus Enum

Typical values: `PREPARING`, `IN_PROGRESS`, `COMPLETED`, `CANCELLED`, etc.

## Repository Trait: ProcessableTrait

Add `ProcessableTrait` to your repository:

```php
<?php

namespace Modules\Order\Repositories;

use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Repositories\Traits\ProcessableTrait;

class OrderRepository extends Repository
{
    use ProcessableTrait;

    public function __construct(Order $model)
    {
        $this->model = $model;
    }
}
```

### Methods

- **setColumnsProcessableTrait** — Collects process input columns
- **getFormFieldsProcessableTrait** — Populates `process_id` and nested process schema fields
- **getProcessId** — Returns or creates the Process ID for the model

## Input Config

Add a process input to your route in `Config/config.php`:

```php
'routes' => [
    'item' => [
        'inputs' => [
            [
                'name' => 'order_process',
                'type' => 'process',
                '_moduleName' => 'Order',
                '_routeName' => 'item',
                'eager' => [],
                'processableTitle' => 'name',
            ],
        ],
    ],
],
```

### Required

- **\_moduleName** — Module name for route resolution
- **\_routeName** — Route name (must have a Processable model)

## Hydrate: ProcessHydrate

`ProcessHydrate` transforms the input into `input-process` schema.

### Requirements

| Key | Default |
|-----|---------|
| color | grey |
| cardVariant | outlined |
| processableTitle | name |
| eager | [] |

### Output

- **type**: `input-process`
- **name**: `process_id`
- **fetchEndpoint**: `admin.process.show` with process ID placeholder
- **updateEndpoint**: `admin.process.update` with process ID placeholder

### Exception

Throws if `_moduleName` or `_routeName` is missing, or if the route's model does not use the Processable trait.
