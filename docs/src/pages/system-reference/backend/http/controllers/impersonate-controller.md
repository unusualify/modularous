---
sidebarPos: 14
sidebarTitle: ImpersonateController
---

# ImpersonateController

**File**: `src/Http/Controllers/ImpersonateController.php`  
**Namespace**: `Unusualify\Modularity\Http\Controllers`  
**Extends**: `Controller`

Allows administrators to impersonate other users for debugging and support. The original admin session is preserved in the session so impersonation can be stopped at any time.

## Constructor

```php
public function __construct(AuthManager $auth)
```

## Methods

### `impersonate(int $id, UserRepository $users): RedirectResponse`

Starts impersonating the user with the given `$id`.

1. Checks that the authenticated user has the `impersonate` permission (via `can:impersonate` gate).
2. Stores the original admin's ID in `session('modularity.impersonate.original_id')`.
3. Logs in as the target user via the modularity guard.
4. Redirects to the dashboard.

### `stopImpersonate(): RedirectResponse`

Ends the impersonation session.

1. Reads the original admin ID from the session.
2. Restores the admin user via the modularity guard.
3. Removes the impersonation key from the session.
4. Redirects to the dashboard.

## Permission

The `impersonate` action is guarded by `can:impersonate`. Make sure this permission is assigned only to super-admin roles.

## Related

- [ImpersonateMiddleware](/system-reference/backend/http/middleware/impersonate) — middleware that injects the impersonation banner
