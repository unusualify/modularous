---
sidebarPos: 7
sidebarTitle: ImpersonateMiddleware
---

# ImpersonateMiddleware

**File**: `src/Http/Middleware/ImpersonateMiddleware.php`  
**Alias**: `modularous.impersonate`  
**Part of**: `modularous.core` group

Activates user impersonation for the current request and shares the impersonation config with the master Blade layout.

## How Impersonation Works

When an admin starts impersonating another user, the target user's ID is stored in the session under the `'impersonate'` key. On every subsequent request this middleware:

1. Checks for `session('impersonate')`.
2. If present, calls `auth()->guard(Modularous::getAuthGuardName())->onceUsingId($targetId)` — this swaps the authenticated user **for the current request only**, without affecting the session's real authenticated user.
3. Composes `modularous::layouts.master` with the impersonation config (`get_modularous_impersonation_config()`), which provides the frontend with the data needed to render the "stop impersonating" banner.

## Impersonation Config

`get_modularous_impersonation_config()` returns an array used by the admin panel to:

- Show or hide the impersonation banner.
- Provide the "stop impersonating" route.
- Display the impersonated user's name.

## Starting / Stopping Impersonation

Impersonation is controlled by two routes:

| Route | Action |
|-------|--------|
| `admin.impersonate` | Sets `session('impersonate', $userId)` |
| `admin.impersonate.stop` | Removes `session('impersonate')` |

These routes are excluded from the intended-URL store in `AuthenticateMiddleware`.

## Notes

- `onceUsingId()` is request-scoped — the real session user is never changed. If the browser is closed and reopened, the impersonation is gone.
- Superadmin-level permission check before setting the session key should be enforced at the controller level, not in this middleware.
