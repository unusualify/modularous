---
sidebarPos: 6
sidebarTitle: RegisterController
---

# RegisterController

**File**: `src/Http/Controllers/Auth/RegisterController.php`  
**Namespace**: `Unusualify\Modularous\Http\Controllers\Auth`  
**Extends**: `Auth\Controller`

Handles direct user registration (without email pre-verification). When the `email_verified_register` config option is enabled, this controller redirects users to the [PreRegisterController](./pre-register-controller) flow instead.

## Methods

### `showForm(): View|RedirectResponse`

Displays the registration form. If `modularous.email_verified_register` is `true`, redirects to the email verification form (`PreRegisterController::showEmailForm`).

### `register(Request $request): JsonResponse|RedirectResponse`

Processes the registration submission:

1. If `email_verified_register` is enabled, returns an error and redirects to the email form.
2. Validates the request via `validator()`.
3. Fires `ModularousUserRegistering` event.
4. Creates a `Company` record (personal if no company name provided).
5. Creates a `User` record attached to the company.
6. Assigns the `client-manager` role.
7. Fires `ModularousUserRegistered` event.
8. Returns a success response with redirect to the registration success page.

### `validator(array $data): Validator`

Returns a validator with the rules from `rules()`.

### `rules(): array`

| Field | Rules |
|-------|-------|
| `name` | required, string, max:255 |
| `surname` | required, string, max:255 |
| `email` | required, string, email, max:255, unique in users table |
| `password` | required, confirmed, meets `Password::defaults()` |

### `success(): View`

Renders a success page after registration, with a button linking to the login form.

## Registration Modes

Modularous supports two registration flows controlled by `modularous.email_verified_register`:

| Mode | Flow |
|------|------|
| **Direct** (`false`) | `RegisterController` — user fills form → account created immediately |
| **Email-verified** (`true`) | `PreRegisterController` → email verification → `CompleteRegisterController` |

## Related

- [PreRegisterController](./pre-register-controller) — email verification step before registration
- [CompleteRegisterController](./complete-register-controller) — completes registration after email verification
- [LoginController](./login-controller) — post-registration login
