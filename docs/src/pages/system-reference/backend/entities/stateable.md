---
sidebarPos: 25
sidebarTitle: Stateable
---

# Stateable

**File**: `src/Entities/Stateable.php`  
**Namespace**: `Unusualify\Modularous\Entities`  
**Extends**: `Illuminate\Database\Eloquent\Model`

Polymorphic pivot model that links a [State](./state) to any model using the `HasStateable` trait. Timestamps are enabled to track when a state was assigned.

## Fillable Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `state_id` | `int` | The state being assigned |
| `stateable_id` | `int` | Target model ID |
| `stateable_type` | `string` | Target model class |

## Relationships

### `state(): BelongsTo`

The state definition. The model class is resolved from `modularous.models.state`, defaulting to `Unusualify\Modularous\Entities\State`.

## Table

Resolved from `modularous.tables.stateables`, defaults to `um_stateables`.

## Related

- [State](./state) — the state definition
- [HasStateable](/system-reference/backend/entity-traits/model-behavior/has-stateable) — trait that manages state relationships
