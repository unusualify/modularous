---
sidebarPos: 29
sidebarTitle: Relationships
---

# Relationships

The `relationships` input type is intended to render a card-based relationship picker (`VInputRelationships`). It is currently an **unfinished placeholder** — the hydrate's `hydrate()` method contains a `dd()` call, which means using this config type will halt execution in a running application.

> [!WARNING]
> `RelationshipsHydrate::hydrate()` contains `dd()`. **Do not use `type: 'relationships'` in production.** This input type is not ready for use.

## Hydrate

**Class:** `RelationshipsHydrate`
**Config type:** `relationships`
**Output type:** `input-relationships` → `VInputRelationships` *(not yet functional)*

## Schema Defaults

These requirements are declared but never reach the frontend because the hydrate halts before completing:

| Key | Default | Description |
|-----|---------|-------------|
| `color` | `'grey'` | Color theme for relationship cards |
| `cardVariant` | `'outlined'` | Vuetify card variant |
| `processableTitle` | `'name'` | Field used as the display title for each related record |
| `eager` | `[]` | Relationships to eager-load |

## See Also

- [Hydrates reference](/system-reference/hydrates) — Resolution table and schema contract
- [Relationships (generics)](/guide/generics/relationships) — Using `connector` for remote data (unrelated to this input type)
