---
sidebarPos: 7
sidebarTitle: ResetPasswordController
---

# ResetPasswordController

**File**: `src/Http/Controllers/Auth/ResetPasswordController.php`  
**Namespace**: `Unusualify\Modularous\Http\Controllers\Auth`  
**Extends**: `Auth\Controller`  
**Traits**: `ResetsPasswords`, `RespondsWithJsonOrRedirect`

Handles the password reset flow after the user clicks the reset link from the [ForgotPasswordController](./forgot-password-controller) email. Also supports a "welcome" flow for new users who receive an invitation link.

## Methods

### `broker(): PasswordBroker`

Returns the password broker configured for the Modularous auth provider via `Modularous::getAuthProviderName()`.

### `showResetForm(Request $request, $token = null): View|RedirectResponse`

Displays the password reset form.

1. Resolves the user from the token via `getUserFromToken()`.
2. Validates the token against the password reset table.
3. Pre-fills the form with the user's email and the token.
4. Renders the reset password view.

If the token is invalid or expired, redirects to the forgot-password page with an error.

### `showWelcomeForm(Request $request, $token = null): View|RedirectResponse`

Displays the password form for new users who received a welcome/invitation email. Unlike `showResetForm`, this method does **not** check token expiry — welcome tokens remain valid until used.

### `reset(Request $request): JsonResponse|RedirectResponse`

Processes the password reset submission:

1. Validates the request (password, confirmation, token, email).
2. Calls `broker()->reset()` with the credentials and a callback that invokes `resetPassword()`.
3. Returns a success or failure response.

### `getUserFromToken(string $token): ?User`

Resolves a user from a password reset token. Supports both:

- **Clear tokens**: direct lookup in the password resets table.
- **Hashed tokens** (Laravel 5.4+): iterates all reset records and checks with `Hash::check()`.

Returns `null` if no matching user is found.

### `sendResetResponse(Request $request, $response): JsonResponse|RedirectResponse`

Returns a success response with translated message and redirect to `$redirectPath`.

### `sendResetFailedResponse(Request $request, $response): JsonResponse|RedirectResponse`

Returns a failure response with the error under the `email` key.

### `success(): View`

Renders a success page confirming the password was reset, with a button linking to the login form.

## Reset Flow

```
User clicks reset link in email
  └─ showResetForm() validates token and renders password form
       └─ User submits new password
            └─ reset() validates and updates password
                 ├─ Success → redirect to login
                 └─ Failure → error shown on form
```

## Welcome Flow

```
Admin invites new user → welcome email sent with token
  └─ showWelcomeForm() renders password form (no token expiry check)
       └─ User sets password
            └─ reset() saves password and logs user in
```

## Related

- [ForgotPasswordController](./forgot-password-controller) — sends the reset email that leads to this controller
- [PasswordController](../password-controller) — alternative password reset controller with a slightly different flow
