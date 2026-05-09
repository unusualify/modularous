---
sidebarPos: 1
sidebarTitle: Auth\Controller
---

# Auth\Controller

**File**: `src/Http/Controllers/Auth/Controller.php`  
**Namespace**: `Unusualify\Modularous\Http\Controllers\Auth`  
**Extends**: `Unusualify\Modularous\Http\Controllers\Controller`  
**Traits**: `AuthFormBuilder`, `ManageUtilities`

Base controller for all authentication workflows (login, registration, password reset). Applies the `modularous.guest` middleware so that authenticated users are redirected away from auth pages, and provides shared utilities for guard resolution and redirect paths.

## Constructor

```php
public function __construct(
    ?Config $config = null,
    ?Redirector $redirector = null,
    ?ViewFactory $viewFactory = null
)
```

1. Calls the parent `Controller::__construct()`.
2. Resolves `Config`, `Redirector`, and `ViewFactory` from the container (or uses injected instances).
3. Sets `$redirectTo` from `modularous.auth_login_redirect_path` config.
4. Applies `modularous.guest` middleware, excluding actions returned by `guestMiddlewareExcept()`.

## Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `Config\|null` | Laravel config repository |
| `$redirector` | `Redirector\|null` | URL redirector |
| `$viewFactory` | `ViewFactory\|null` | Blade view factory |
| `$redirectTo` | `string` | Post-authentication redirect path |

## Methods

### `guestMiddlewareExcept(): array`

Returns an array of method names that should be excluded from the `modularous.guest` middleware. Override in subclasses to allow authenticated users to access specific actions (e.g. `LoginController` excludes `logout`).

Default implementation returns an empty array.

### `guard()`

Returns the authentication guard configured for Modularous via `Modularous::getAuthGuardName()`.

### `redirectPath(): string`

Returns the value of `$redirectTo` — used by Laravel's authentication traits to determine where to send the user after a successful auth action.

## Inheritance

All authentication controllers extend this class:

```
Auth\Controller
├── LoginController
├── RegisterController
├── ForgotPasswordController
├── PreRegisterController
├── CompleteRegisterController
└── ResetPasswordController
```

## Related

- [Controller](../controller) — root Modularous controller (parent of this class)
- [LoginController](./login-controller) — login and 2FA
- [RegisterController](./register-controller) — user registration
