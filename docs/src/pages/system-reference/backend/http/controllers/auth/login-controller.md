---
sidebarPos: 4
sidebarTitle: LoginController
---

# LoginController

**File**: `src/Http/Controllers/Auth/LoginController.php`  
**Namespace**: `Unusualify\Modularity\Http\Controllers\Auth`  
**Extends**: `Auth\Controller`  
**Traits**: `AuthenticatesUsers`, `HandlesOAuth`

Handles user login with email/password, Google 2FA verification, OAuth provider authentication, and session logout.

## Constructor

```php
public function __construct(
    Config $config,
    AuthManager $authManager,
    Encrypter $encrypter,
    Redirector $redirector,
    ViewFactory $viewFactory
)
```

Sets up authentication manager, encrypter, redirector, and view factory. Configures `$redirectTo` from `modularity.auth_login_redirect_path`.

## Properties

| Property | Type | Description |
|----------|------|-------------|
| `$authManager` | `AuthManager` | Laravel auth manager |
| `$encrypter` | `Encrypter` | Encryption service (used for 2FA) |

## Methods

### `showForm(): View`

Renders the login form view using the `AuthFormBuilder` trait to build the form schema and layout.

### `showLogin2FaForm(): View`

Renders the Google 2FA code entry form. Shown after initial email/password authentication when the user has 2FA enabled.

### `logout(Request $request): RedirectResponse`

Logs the user out, invalidates the session, regenerates the CSRF token, and redirects to the login form.

### `authenticated(Request $request, $user)`

Called by Laravel's `AuthenticatesUsers` trait after successful credential validation. Delegates to `afterAuthentication()`.

### `afterAuthentication(Request $request, $user)`

Post-login handler with two paths:

1. **2FA enabled**: logs the user out temporarily, stores their ID in `session('2fa:user:id')`, and redirects to the 2FA form.
2. **No 2FA**: stores the user's timezone in the session (if provided) and returns a success response with redirect URL.

Returns JSON for Inertia/AJAX requests or a redirect for traditional requests.

### `login2Fa(Request $request): RedirectResponse`

Verifies the Google 2FA one-time password. On success, logs the user in and redirects to the intended URL. On failure, redirects back to the 2FA form with an error.

### `redirectTo(): string`

Returns the dashboard route URL.

### `sendFailedLoginResponse(Request $request): JsonResponse`

Returns a JSON error response with the `auth.failed` translation message for AJAX requests, or throws a `ValidationException` for traditional requests.

## Guest Middleware

The `logout` action is excluded from the `modularity.guest` middleware via `guestMiddlewareExcept()`, allowing authenticated users to access the logout route.

## OAuth Flow

The `HandlesOAuth` trait adds OAuth provider support:

| Method | Description |
|--------|-------------|
| `redirectToProvider($provider)` | Redirects to the OAuth provider (e.g. Google) |
| `handleProviderCallback($provider)` | Processes the OAuth callback — links or creates user |
| `showPasswordForm()` | Prompts for password when linking OAuth to existing account |
| `linkProvider()` | Confirms password and links the OAuth provider |

## Related

- [Auth\Controller](./controller) — base auth controller
- [ForgotPasswordController](./forgot-password-controller) — password recovery
- [RegisterController](./register-controller) — new account registration
