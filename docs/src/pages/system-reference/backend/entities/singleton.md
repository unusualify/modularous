---
sidebarPos: 22
sidebarTitle: Singleton
---

# Singleton

**File**: `src/Entities/Singleton.php`  
**Namespace**: `Unusualify\Modularous\Entities`  
**Extends**: `Model`

A single-record model for data that should only ever have one row per type. The `singleton_type` discriminator identifies the kind of singleton, and `content` stores its payload as JSON.

## Fillable Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `id` | `int` | Primary key |
| `singleton_type` | `string` | Type discriminator |
| `content` | `array` | Singleton data (JSON cast) |

## Table

Resolved from `modularous.tables.singletons`, defaults to `modularous_singletons`.

## Related

- [IsSingular](/system-reference/backend/entity-traits/singletons/is-singular) — trait that applies singleton behaviour to a module route
