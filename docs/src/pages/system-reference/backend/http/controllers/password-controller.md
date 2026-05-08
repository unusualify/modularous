---
sidebarPos: 18
sidebarTitle: PasswordController
---

# PasswordController

**File**: `src/Http/Controllers/PasswordController.php`  
**Namespace**: `Unusualify\Modularity\Http\Controllers`  
**Extends**: `Controller`  
**Traits**: `ManageUtilities`, `MakesResponses`, `ResetsPasswords`  
**Middleware**: `modularity.guest`

Handles two distinct password flows: the **forgot-password reset** flow (user already has an account) and the **first-time password generation** flow (new user completing registration).

## Constructor

```php
public function __construct()
```

Applies the `modularity.guest` middleware to all actions — only unauthenticated users may access these routes.

## Methods

### `showForm(Request $request, string $token): View`

Displays the password entry form. The `$token` is validated against the password reset table before the form is shown. The form schema is loaded from `getFormDraft('reset_password_form')`.

Used for both the forgot-password link (from email) and the welcome link (new user invitation).

### `savePassword(Request $request): JsonResponse|RedirectResponse`

Validates the submitted password (with confirmation) and calls `resetPassword()`.

On success:
- Marks the user's email as verified.
- Logs the user in via the modularity guard.
- Returns JSON for Inertia requests or redirects for traditional requests.

### `broker(): PasswordBroker`

Returns the Laravel password broker configured for Modularous.

### `guard(): StatefulGuard`

Returns the modularity authentication guard.

### `resetPassword($user, string $password): void`

Sets the user's password, saves the model, and logs the user in.

## Related

- [Auth\ForgotPasswordController](./auth/forgot-password-controller) — sends the reset email
- [Auth\ResetPasswordController](./auth/reset-password-controller) — the standard Laravel reset flow
