---
sidebarPos: 2
sidebarTitle: AuthenticateMiddleware
---

# AuthenticateMiddleware

**File**: `src/Http/Middleware/AuthenticateMiddleware.php`  
**Alias**: `modularous.auth`  
**Extends**: `Illuminate\Auth\Middleware\Authenticate`

Extends Laravel's built-in `Authenticate` middleware with two Modularous-specific behaviours: intended-URL preservation before the redirect and JSON 401 handling for Inertia/AJAX requests.

## Behaviour

### Standard HTML requests

When an unauthenticated browser request hits a protected route:

1. The current URL is saved to `session('url.intended')` via `session()->put('url.intended', url()->previous())`.
2. The user is redirected to the admin login route (`admin.login.form`).

**Excluded routes** — the intended URL is NOT stored when the current route is any of:

| Route name (prefixed) |
|-----------------------|
| `login.form`, `login`, `logout` |
| `register.form`, `register`, `register.success` |
| `password.reset.*` |
| `impersonate`, `impersonate.stop` |

This prevents the login page itself (or other auth pages) from being set as the post-login destination.

### JSON / Inertia requests

When the request expects JSON (`Accept: application/json`):

- The `Referer` header is stored in `session('url.intended')`.
- Returns a `401 JSON` response:
  ```json
  { "message": "Unauthenticated.", "mode": "experimental" }
  ```

## Usage

Applied via the `web.auth` and `api.auth` middleware groups:

```php
Route::middlewareGroup('web.auth', [
    'web',
    'modularous.auth:modularous',
]);
```

The guard name is injected from `Modularous::getAuthGuardName()` (default: `'modularous'`).

## Notes

- After a successful login, the `RedirectService` or Laravel's `url()->intended()` uses the stored URL to redirect the user back.
- The `mode: experimental` field in the JSON 401 is a placeholder for future Inertia partial-reload handling.
