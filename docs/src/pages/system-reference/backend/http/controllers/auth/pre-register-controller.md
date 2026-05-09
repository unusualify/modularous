---
sidebarPos: 5
sidebarTitle: PreRegisterController
---

# PreRegisterController

**File**: `src/Http/Controllers/Auth/PreRegisterController.php`  
**Namespace**: `Unusualify\Modularous\Http\Controllers\Auth`  
**Extends**: `Auth\Controller`  
**Traits**: `SendsEmailVerificationRegister`

First step in the email-verified registration flow. Collects the user's email address, sends a verification link, and waits for the user to confirm before proceeding to account creation via [CompleteRegisterController](./complete-register-controller).

## Constructor

```php
public function __construct(?Application $app = null)
```

Calls the parent `Auth\Controller` constructor.

## Methods

### `broker()`

Returns the register broker via `Register::broker()`.

### `showEmailForm(): View`

Renders the pre-registration form where the user enters their email address. The form is built via `AuthFormBuilder::buildAuthViewData('pre_register')`.

### `sendVerificationLinkEmail(Request $request)` *(from trait)*

Validates the email, sends a verification link through the register broker, and fires the `ModularousUserVerification` event on success.

**Response on success**: redirects to the login page with a modal confirming the email was sent, or to a dedicated success page depending on the `MODULAROUS_USE_REGISTRATION_REDIRECT_WITH_MODAL` environment variable.

**Response on failure**: returns a warning with the broker error message.

### `showSuccessForm()` *(from trait)*

Renders a success page confirming the verification email was sent, with a button linking back to the login form.

## Email-Verified Registration Flow

```
1. User visits registration page
   └─ RegisterController redirects to PreRegisterController (when email_verified_register = true)
        └─ showEmailForm() renders email-only form

2. User submits email
   └─ sendVerificationLinkEmail() sends signed verification link
        └─ Success modal or success page displayed

3. User clicks verification link in email
   └─ CompleteRegisterController::showCompleteRegisterForm() renders full registration form

4. User completes registration
   └─ CompleteRegisterController::completeRegister() creates account
```

## Related

- [CompleteRegisterController](./complete-register-controller) — second step: completes account creation after email verification
- [RegisterController](./register-controller) — direct registration (when email verification is disabled)
