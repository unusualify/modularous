---
sidebarPos: 1
sidebarTitle: Assignable
---

# Assignable

**Namespace**: `Unusualify\Modularous\Entities\Traits\Assignable`

Tracks user/role assignments for a model via `Assignment` morph records. Includes `AssignableScopes` for filtering by assignment status. Deletes or force-deletes assignments in sync with the parent model's deletion.

---

## Boot Behavior

| Event | Action |
|-------|--------|
| `deleting` | Soft-deletes all `assignments()` (or force-deletes if SoftDeletes is not used) |
| `forceDeleting` | Force-deletes all `assignments()` |

---

## Relationships

```php
public function assignments(): MorphMany   // → Assignment model, all assignments
public function lastAssignment(): MorphOne // → most recent Assignment (latest created_at)
```

---

## Appended Attributes

Appended via `initializeAssignable()`:

| Attribute | Type | Description |
|-----------|------|-------------|
| `active_assignee_name` | `string\|null` | Display name of the current assignee (from `lastAssignment->assignee->name`) |
| `active_assigner_name` | `string\|null` | Display name of the user who made the last assignment |
| `active_assignment_status` | `string\|null` | HTML chip with the last assignment's status, icon, and color |

---

## Scopes

Provided by `AssignableScopes`:

| Scope | Description |
|-------|-------------|
| `scopeAssignedTo($userId)` | Models assigned to a specific user |
| `scopeUnassigned()` | Models with no active assignment |

---

## Usage

```php
use Unusualify\Modularous\Entities\Traits\Assignable;

class Task extends Model
{
    use Assignable;
}

// Create an assignment
$task->assignments()->create(['assignee_id' => $user->id, 'assigner_id' => $admin->id]);

// Read last assignment
$task->lastAssignment;
$task->active_assignee_name;
$task->active_assignment_status;

// Filter queries
Task::assignedTo($userId)->get();
Task::unassigned()->get();
```
