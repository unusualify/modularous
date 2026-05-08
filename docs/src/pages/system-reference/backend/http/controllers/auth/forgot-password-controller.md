---
sidebarPos: 3
sidebarTitle: ForgotPasswordController
---

# ForgotPasswordController

**File**: `src/Http/Controllers/Auth/ForgotPasswordController.php`  
**Namespace**: `Unusualify\Modularity\Http\Controllers\Auth`  
**Extends**: `Auth\Controller`  
**Traits**: `SendsPasswordResetEmails`

Sends password reset emails using Laravel's built-in password broker. The user enters their email address and receives a link to the [ResetPasswordController](./reset-password-controller).

## Methods

### `broker(): PasswordBroker`

Returns the password broker configured for the Modularous auth provider via `Modularity::getAuthProviderName()`.

### `showLinkRequestForm(): View`

Renders the "forgot password" form where the user enters their email address. The form schema is built via `AuthFormBuilder::buildAuthViewData('forgot_password')`.

### `sendResetLinkResponse(Request $request, $response): JsonResponse|RedirectResponse`

Called when the reset link is sent successfully.

- **JSON (Inertia/AJAX)**: returns a success message with `MessageStage::SUCCESS` variant.
- **Traditional**: redirects back with a `status` flash message.

### `sendResetLinkFailedResponse(Request $request, $response): JsonResponse|RedirectResponse`

Called when the reset link cannot be sent (e.g. email not found).

- **JSON**: returns the error message with `MessageStage::WARNING` variant.
- **Traditional**: redirects back with the email input preserved and an `email` error.

## Flow

```
User clicks "Forgot Password" on login page
  └─ showLinkRequestForm() renders the email form
       └─ User submits email
            └─ SendsPasswordResetEmails::sendResetLinkEmail() (Laravel trait)
                 ├─ Success → sendResetLinkResponse() → user checks email
                 └─ Failure → sendResetLinkFailedResponse() → error shown
```

## Related

- [ResetPasswordController](./reset-password-controller) — handles the reset link the user receives
- [PasswordController](../password-controller) — alternative password reset and first-time password generation
- [LoginController](./login-controller) — links to this controller from the login form
