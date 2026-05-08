---
sidebarPos: 8
sidebarTitle: StoreRoleRequest
---

# StoreRoleRequest

**File**: `src/Http/Requests/StoreRoleRequest.php`
**Namespace**: `Unusualify\Modularity\Http\Requests`
**Extends**: `Illuminate\Foundation\Http\FormRequest`

Validates the payload for creating a new Spatie role.

## Rules

| Field | Rule |
|-------|------|
| `name` | `required`, unique on the `roles` table, minimum 4 characters |

## Authorization

`authorize()` returns `true` — access control is expected to be enforced at the controller / middleware level rather than inside this request.

## Notes

- Extends `FormRequest` directly; no model-aware translation logic is needed for role names.
- The 4-character minimum is a convention to discourage ambiguous role names (e.g. `qa`, `it`). Adjust via override if your application requires shorter role identifiers.

## Related

- Spatie Permission package — provides the underlying `roles` table and `Role` model
- [`RoleResource`](#) — the API resource used to render these records
