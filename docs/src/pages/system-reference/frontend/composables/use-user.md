---
sidebarTitle: useUser
---

# useUser

Provides reactive state about the authenticated user — guest status, roles, timezone, company validity — and proxies the authorization helpers from `useAuthorization`.

**File:** `vue/src/js/hooks/useUser.js`

---

## Usage

```js
import { useUser } from '@/hooks'

const {
  isGuest,
  isSuperAdmin,
  isClient,
  timezone,
  validCompany,
  showBillingBanner,
  can,
  isYou,
  hasRoles
} = useUser()
```

```html
<template v-if="!isGuest">
  <span>Welcome back</span>
</template>

<v-btn v-if="can('manage-billing')">Billing</v-btn>
```

## Returns

### State

| Name | Type | Description |
|------|------|-------------|
| `isGuest` | `ComputedRef<Boolean>` | `true` when no user is authenticated (`store.getters.isGuest`) |
| `isSuperAdmin` | `ComputedRef<Boolean>` | `true` when the user has the super-admin role |
| `isClient` | `ComputedRef<Boolean>` | `true` when the user has the client role |
| `timezone` | `ComputedRef<String>` | User's timezone string (e.g. `'Europe/Istanbul'`), defaults to `'Europe/London'` |
| `validCompany` | `ComputedRef<Boolean>` | `true` when the user's company has passed validation (`store.state.user.valid_company`) |
| `showBillingBanner` | `ComputedRef<Boolean>` | `true` when the billing banner should be displayed (`store.state.user.profile.show_billing_banner`) |

### Proxied from `useAuthorization`

| Name | Signature | Description |
|------|-----------|-------------|
| `can` | `(permission, moduleName?) => Boolean` | Permission check — see [useAuthorization](/system-reference/frontend/composables/use-authorization) |
| `isYou` | `(id) => Boolean` | Returns `true` when `id` matches the authenticated user's id |
| `hasRoles` | `(roles: String\|Array) => Boolean` | Returns `true` when the user has at least one of the given roles |

## Notes

- `useUser` is a convenience wrapper — it combines Vuex user state with the authorization helpers so components only need one import instead of two.
- For permission-only logic, prefer `useAuthorization` directly to keep the dependency minimal.

## See Also

- [useAuthorization](/system-reference/frontend/composables/use-authorization) — full permission and role API
