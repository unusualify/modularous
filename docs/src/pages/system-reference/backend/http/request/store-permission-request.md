---
sidebarPos: 7
sidebarTitle: StorePermissionRequest
---

# StorePermissionRequest

**File**: `src/Http/Requests/StorePermissionRequest.php`
**Namespace**: `Unusualify\Modularity\Http\Requests`
**Extends**: `Illuminate\Foundation\Http\FormRequest`

Validates the payload for creating a new Spatie permission.

## Rules

| Field | Rule |
|-------|------|
| `name` | `required`, unique on the `permissions` table |

## Authorization

`authorize()` returns `true` — any authenticated user allowed to reach the controller may submit this request. Gate the controller action itself (with `permission:permissions.create` or similar) if you need stricter access control.

## Notes

- Extends `FormRequest` directly rather than one of Modularous's base classes, because the payload is a flat plain-text field with no model-aware translation logic required.
- The unique check is hard-coded against the `permissions` table name. If you publish and rename the Spatie tables, update this class accordingly.

## Related

- Spatie Permission package — provides the underlying `permissions` table and `Permission` model
- [`PermissionResource`](#) — the API resource used to render these records
