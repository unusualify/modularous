---
sidebarPos: 20
sidebarTitle: Revision
---

# Revision

**File**: `src/Entities/Revision.php`  
**Namespace**: `Unusualify\Modularity\Entities`  
**Extends**: `Illuminate\Database\Eloquent\Model`

Abstract base for revision-tracking models. Each module that enables the `HasRevisions` trait generates a `{Model}Revision` class extending this. Stores a JSON snapshot of the model's state at the time of the revision.

## Properties

| Property | Type | Description |
|----------|------|-------------|
| `$fillable` | `array` | `['payload', 'user_id', '{model}_id']` — foreign key is auto-appended |
| `$with` | `array` | `['user']` — always eager-loads the user who made the change |

## Constructor

Automatically appends the parent model's foreign key to `$fillable` by deriving it from the class name (e.g. `PostRevision` → `post_id`).

## Relationships

### `user(): BelongsTo`

The user who created this revision.

## Accessors

### `by_user: string`

Returns the revision author's name, or `'System'` when no user is associated.

## Related

- [HasRevisions](/system-reference/backend/entity-traits/secondary/has-revisions) entity trait — adds revision tracking to a model
