---
sidebarPos: 23
sidebarTitle: Spread
---

# Spread

**File**: `src/Entities/Spread.php`  
**Namespace**: `Unusualify\Modularity\Entities`  
**Extends**: `Illuminate\Database\Eloquent\Model`

Stores dynamic key-value JSON data attached to a parent model via a polymorphic relationship. Acts as an extension mechanism to add arbitrary fields to a model without schema changes.

## Fillable Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `spreadable_id` | `int` | Parent model ID |
| `spreadable_type` | `string` | Parent model class |
| `content` | `array` | Dynamic data (JSON cast) |

## Relationships

### `spreadable(): MorphTo`

The parent model this spread data belongs to.

## Table

Resolved from `modularity.tables.spreads`, defaults to `um_spreads`.

## Related

- [HasSpreadable](/system-reference/backend/entity-traits/model-behavior/has-spreadable) — trait that adds spread support to models
- [Company](./company) — uses spreads for flexible billing fields
