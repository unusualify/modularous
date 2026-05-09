---
sidebarPos: 3
sidebarTitle: Authorization
---

# Authorization

**File**: `src/Entities/Authorization.php`  
**Namespace**: `Unusualify\Modularous\Entities`  
**Extends**: `Illuminate\Database\Eloquent\Model`

Polymorphic pivot model that links an authorized entity (e.g. a user) to an authorizable model. Dispatches notification events when records are created or the authorized party changes.

## Fillable Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `authorized_id` | `int` | The entity that has authorization |
| `authorized_type` | `string` | Authorized entity's class |
| `authorizable_id` | `int` | The model being authorized on |
| `authorizable_type` | `string` | Authorizable model's class |

## Boot Events

| Event | Action |
|-------|--------|
| `created` | Dispatches `AuthorizableCreated` |
| `updated` | Dispatches `AuthorizableUpdated` (only when `authorized_id` or `authorized_type` changes) |

## Relationships

### `authorized(): MorphTo`

The entity (typically a user) that holds authorization.

### `authorizable(): MorphTo`

The model this authorization applies to.

## Table

Resolved from `modularous.tables.authorizations`, defaults to `modularous_authorizations`.

## Related

- [HasAuthorizable](/system-reference/backend/entity-traits/relationships/has-authorizable) — adds authorization support to models
