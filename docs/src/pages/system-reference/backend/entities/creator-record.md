---
sidebarPos: 8
sidebarTitle: CreatorRecord
---

# CreatorRecord

**File**: `src/Entities/CreatorRecord.php`  
**Namespace**: `Unusualify\Modularity\Entities`  
**Extends**: `Illuminate\Database\Eloquent\Model`

Records the creator of a model instance. Used by the `HasCreator` trait to track which user (and auth guard) created a given record. Timestamps are disabled.

## Fillable Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `creator_type` | `string` | Creator model class |
| `creator_id` | `int` | Creator model ID |
| `guard_name` | `string` | Auth guard used at creation time |
| `creatable_type` | `string` | Created model class |
| `creatable_id` | `int` | Created model ID |

## Relationships

### `creatable(): MorphTo`

The model that was created.

### `creator(): MorphTo`

The user/entity who created the model.

## Table

Resolved from `modularity.tables.creator_records`, defaults to `um_creator_records`.

## Related

- [HasCreator](/system-reference/backend/entity-traits/relationships/has-creator) — trait that writes creator records
