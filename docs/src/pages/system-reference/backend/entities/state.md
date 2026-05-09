---
sidebarPos: 24
sidebarTitle: State
---

# State

**File**: `src/Entities/State.php`  
**Namespace**: `Unusualify\Modularous\Entities`  
**Extends**: `Illuminate\Database\Eloquent\Model`  
**Traits**: `HasTranslation`

A translatable state definition (e.g. Draft, Active, In Review, Closed). States are linked to models through [Stateable](./stateable) pivot records. Translations are always eager-loaded.

## Fillable Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `published` | `bool` | Whether the state is active |
| `code` | `string` | Machine-readable code |
| `icon` | `string` | Icon identifier |
| `color` | `string` | Colour for UI display |

## Translated Attributes

| Attribute | Description |
|-----------|-------------|
| `name` | Human-readable state name |
| `active` | Translation active flag |

## Table

Resolved from `modularous.tables.states`, defaults to `um_states`.

## Related

- [Stateable](./stateable) — morph pivot linking states to models
- [HasStateable](/system-reference/backend/entity-traits/model-behavior/has-stateable) — trait that adds state support to models
