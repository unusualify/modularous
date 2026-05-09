---
sidebarPos: 2
sidebarTitle: Assignment
---

# Assignment

**File**: `src/Entities/Assignment.php`  
**Namespace**: `Unusualify\Modularous\Entities`  
**Extends**: `Illuminate\Database\Eloquent\Model`  
**Traits**: `SoftDeletes`, `AssignmentScopes`, `HasFileponds`

Task assignment model with polymorphic relationships to the assignable target, assignee, and assigner. Tracks status via the `AssignmentStatus` enum with due dates, completion timestamps, and file attachments.

## Fillable Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `assignable_id` | `int` | Target model ID |
| `assignable_type` | `string` | Target model class |
| `assignee_id` | `int` | Assigned-to user/model ID |
| `assignee_type` | `string` | Assigned-to model class |
| `assigner_id` | `int` | Assigned-by user ID (auto-set from auth) |
| `assigner_type` | `string` | Assigned-by model class (auto-set from auth) |
| `status` | `AssignmentStatus` | Current status (enum cast) |
| `title` | `string` | Assignment title |
| `description` | `string` | Description/instructions |
| `due_at` | `datetime` | Deadline |
| `accepted_at` | `datetime` | When the assignee accepted |
| `completed_at` | `datetime` | When the assignment was completed |

## Boot Events

| Event | Action |
|-------|--------|
| `creating` | Auto-fills `assigner_id` and `assigner_type` from the authenticated user |
| `created` | Dispatches `AssignmentCreated` |
| `updated` | Dispatches `AssignmentUpdated` |

## Relationships

### `assignable(): MorphTo`

The target entity this assignment refers to.

### `assignee(): MorphTo`

The user or model the task is assigned to.

### `assigner(): MorphTo`

The user who created the assignment.

## Accessors

| Accessor | Type | Description |
|----------|------|-------------|
| `assignee_name` | `string` | Assignee's name or email |
| `assignee_avatar` | `string` | Assignee's avatar URL |
| `assigner_name` | `string` | Assigner's name or email |
| `status_label` | `string` | Human-readable status |
| `status_color` | `string` | Status colour for the UI |
| `status_icon` | `string` | Status icon identifier |
| `status_icon_color` | `string` | Icon colour |
| `status_interval_description` | `string` | Time-related label (due/completed/updated) with formatted date |
| `status_vuetify_icon` | `string` | Vuetify `<v-icon>` HTML snippet |
| `attachments` | `Collection` | Filepond uploads with role `attachments` |
| `preliminaries` | `Collection` | Filepond uploads with role `preliminaries` |

## AssignmentStatus Enum

`PENDING`, `ACCEPTED`, `COMPLETED`, `CANCELLED`, `REJECTED` — each value provides `label()`, `color()`, `icon()`, `iconColor()`, and `timeIntervalDescription()`.

## Related

- [Assignable](/system-reference/backend/entity-traits/relationships/assignable) — trait to make a model assignable
- [Filepond](./filepond) — file attachments
