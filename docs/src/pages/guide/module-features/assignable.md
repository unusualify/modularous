---
outline: deep
sidebarPos: 7
---

# Assignable

The Assignable feature lets you assign records (e.g. tasks, tickets) to users or roles. It follows the triple pattern: Entity trait + Repository trait + Hydrate.

## Entity Trait: Assignable

Add the `Assignable` trait to your model:

```php
<?php

namespace Modules\Task\Entities;

use Unusualify\Modularity\Entities\Model;
use Unusualify\Modularity\Entities\Traits\Assignable;

class Task extends Model
{
    use Assignable;
}
```

### Relationships and Accessors

- **assignments()** — morphMany to `Assignment` (all assignments for the record)
- **lastAssignment()** — morphOne to the latest assignment
- **active_assignee_name** — appended attribute; name of the current assignee
- **active_assigner_name** — appended attribute; name of the user who assigned
- **active_assignment_status** — appended attribute; status chip HTML

### Boot Logic

On delete, assignments are soft-deleted (if the model uses `SoftDeletes`) or force-deleted. On force-delete, assignments are force-deleted as well.

## Repository Trait: AssignmentTrait

Add `AssignmentTrait` to your repository:

```php
<?php

namespace Modules\Task\Repositories;

use Unusualify\Modularity\Repositories\Repository;
use Unusualify\Modularity\Repositories\Traits\AssignmentTrait;

class TaskRepository extends Repository
{
    use AssignmentTrait;

    public function __construct(Task $model)
    {
        $this->model = $model;
    }
}
```

### Methods

- **setColumnsAssignmentTrait** — Collects assignment input columns from route inputs
- **getFormFieldsAssignmentTrait** — Populates form fields with assignable object key
- **filterAssignmentTrait** — Applies `everAssignedToYourRoleOrHasAuthorization` scope
- **getTableFiltersAssignmentTrait** — Returns table filters (my-assignments, your-role-assignments, completed, pending, etc.)
- **getAssignments** — Fetches assignments for a given assignable ID

## Input Config

Add an assignment input to your route in `Config/config.php`:

```php
'routes' => [
    'item' => [
        'inputs' => [
            [
                'name' => 'assignee',
                'type' => 'assignment',
                'assigneeType' => \Modules\SystemUser\Entities\User::class,  // optional; defaults to route model
                'scopeRole' => ['admin', 'manager'],  // optional; filter assignees by Spatie role
                'acceptedExtensions' => ['pdf'],       // optional; for attachments
                'max-attachments' => 3,               // optional
            ],
        ],
    ],
],
```

## Hydrate: AssignmentHydrate

`AssignmentHydrate` transforms the input into `input-assignment` schema.

### Requirements

| Key | Default |
|-----|---------|
| name | assignable_id |
| noSubmit | true |
| col | ['cols' => 12] |
| default | null |

### Output

- **type**: `input-assignment`
- **assigneeType**: Resolved from input or route model
- **assignableType**: Resolved from route model
- **fetchEndpoint**: URL for fetching assignments
- **saveEndpoint**: URL for creating assignments
- **filepond**: Embedded Filepond schema for attachments (default: pdf, max 3)

### Role Scoping

If `scopeRole` is set and the assignee model uses Spatie `HasRoles`, the hydrate filters assignees by those roles.
