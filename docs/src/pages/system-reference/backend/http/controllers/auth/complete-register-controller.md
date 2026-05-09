---
sidebarPos: 2
sidebarTitle: CompleteRegisterController
---

# CompleteRegisterController

**File**: `src/Http/Controllers/Auth/CompleteRegisterController.php`  
**Namespace**: `Unusualify\Modularous\Http\Controllers\Auth`  
**Extends**: `Auth\Controller`  
**Traits**: `CreateVerifiedEmailAccount`, `RespondsWithJsonOrRedirect`

Second step in the email-verified registration flow. After the user verifies their email via [PreRegisterController](./pre-register-controller), this controller displays the full registration form and creates the user account.

## Methods

### `broker()`

Returns the register broker via `Register::broker()`.

### `showCompleteRegisterForm(Request $request, $token = null): View|RedirectResponse`

Displays the complete registration form if the email/token combination is valid.

1. Extracts `token` from the route and `email` from the query string.
2. Validates the token exists for the email via `Register::broker('register_verified_users')->emailTokenExists()`.
3. Fires the `ModularousUserRegistering` event.
4. Builds the form schema from `getFormDraft('complete_register_form')`, pre-filling fields from the request (excluding password fields).
5. Renders the registration view.

If the token is invalid or expired, redirects to the email form with an error.

### `completeRegister(Request $request): JsonResponse|RedirectResponse`

Processes the registration form submission:

1. Validates all fields via the `CreateVerifiedEmailAccount` rules.
2. Calls `broker()->register()` with the submitted credentials.
3. On success, calls `registerEmail()` which:
   - Creates the `User` record with hashed password and verified email.
   - Creates a `Company` record.
   - Assigns the `client-manager` role.
   - Fires `ModularousUserRegistered` and `VerifiedEmailRegister` events.
   - Logs the user in automatically.
4. Returns a success or failure response.

### `sendRegisterResponse(Request $request, $response): JsonResponse|RedirectResponse`

Returns a success response with translated message and redirect path.

### `sendRegisterFailedResponse(Request $request, $response): JsonResponse|RedirectResponse`

Returns a failure response with the error message under the `email` key.

## Validation Rules

| Field | Rules |
|-------|-------|
| `token` | required |
| `email` | required, email |
| `name` | required, no consecutive spaces |
| `surname` | required, no consecutive spaces |
| `company` | required |
| `password` | required, confirmed |

## Related

- [PreRegisterController](./pre-register-controller) — first step: sends the email verification link
- [RegisterController](./register-controller) — direct registration (when email verification is disabled)
