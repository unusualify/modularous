---
sidebarPos: 18
sidebarTitle: RelatedItem
---

# RelatedItem

**File**: `src/Entities/RelatedItem.php`  
**Namespace**: `Unusualify\Modularity\Entities`  
**Extends**: `Illuminate\Database\Eloquent\Model`

Polymorphic many-to-many pivot model for relating any two models to each other. Has no primary key and no auto-incrementing — it uses a composite of the morph columns. Timestamps are disabled.

## Configuration

| Property | Value |
|----------|-------|
| `$guarded` | `[]` (mass-assignable) |
| `$primaryKey` | `null` |
| `$incrementing` | `false` |
| `$timestamps` | `false` |

## Relationships

### `related(): MorphTo`

The related model.

### `subject(): MorphTo`

The subject model (the model that "has" related items).

## Table

Resolved from `modularity.related_table`, defaults to `twill_related`.

## Related

- [HasRelated](/system-reference/backend/entity-traits/secondary/has-related) — trait that adds related items to a model
