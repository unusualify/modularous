---
sidebarTitle: useAuthorization
---

# useAuthorization

Provides reactive permission and role checks against the authenticated user stored in Vuex.

**File:** `vue/src/js/hooks/useAuthorization.js`

---

## Usage

```js
import { useAuthorization } from '@/hooks'

const { can, hasRoles, isYou } = useAuthorization()

// Check a global permission
if (can('view-reports')) { ... }

// Check a module-scoped permission (resolves to "{module}_{permission}")
if (can('edit', 'Blog')) { ... }

// Check whether the authenticated user matches an ID
if (isYou(item.user_id)) { ... }

// Check role membership
if (hasRoles('admin,editor')) { ... }
```

## Returns

| Name | Signature | Description |
|------|-----------|-------------|
| `can` | `(permission, moduleName?) => Boolean` | Returns `true` if the user is super-admin OR has the named permission. When `moduleName` is provided the permission key is `{moduleName}_{permission}`. |
| `hasRoles` | `(roles: String\|Array) => Boolean` | Returns `true` if the user has at least one of the given roles. Accepts a comma-separated string or an array. |
| `isYou` | `(id) => Boolean` | Returns `true` if `id` matches `store.getters.userProfile.id`. |

## How permissions are resolved

```
can('edit', 'Blog')
  → looks up store.getters.userPermissions['Blog_edit']
  → OR store.getters.isSuperAdmin (super-admin bypasses all checks)
```

## Notes

- Permission names are case-sensitive and must match exactly how they are defined on the backend.
- Super-admins (`isSuperAdmin` getter) bypass all `can()` checks.

## See Also

- [useAuthorization in action items](/system-reference/frontend/composables/use-item-actions) — `validateAction` uses user conditions
- Backend authorization middleware: `AuthorizationMiddleware`
