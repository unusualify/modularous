---
sidebarPos: 26
sidebarTitle: Tag
---

# Tag

**File**: `src/Entities/Tag.php`  
**Namespace**: `Unusualify\Modularity\Entities`  
**Extends**: `Cartalyst\Tags\IlluminateTag`

Tag model extending Cartalyst's tag package with locale support. Tags are shared across models but scoped by namespace and locale.

## Fillable Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `name` | `string` | Tag display name |
| `slug` | `string` | URL-safe slug |
| `count` | `int` | Usage count |
| `namespace` | `string` | Tag namespace for scoping |
| `locale` | `string` | Tag locale |

## Configuration

Uses [Tagged](./tagged) as the pivot model (`$taggedModel`).

## Table

Resolved from `modularity.tables.tags`.

## Related

- [Tagged](./tagged) — the pivot model for taggable relationships
- [TagController](/system-reference/backend/http/controllers/tag-controller) — search and creation endpoints
- [Model](./model) — base model that includes `TaggableTrait`
