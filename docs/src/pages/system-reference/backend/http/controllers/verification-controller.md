---
sidebarPos: 23
sidebarTitle: VerificationController
---

# VerificationController

**File**: `src/Http/Controllers/VerificationController.php`  
**Namespace**: `Unusualify\Modularity\Http\Controllers`  
**Extends**: `Controller`  
**Traits**: `ManageForm`

Handles email address verification for authenticated users. Provides an endpoint to fulfil a signed verification link and another to re-send the verification email.

## Methods

### `verify(EmailVerificationRequest $request): View`

Fulfils an email verification request. Uses Laravel's built-in `EmailVerificationRequest` which validates the signature and user ID automatically.

On success, renders a success view with the following state:

| Variable | Value |
|----------|-------|
| `status` | `'success'` |
| `title` | Verification complete message |
| `description` | Verification complete description |
| `button_url` | Dashboard route |

### `send(Request $request): RedirectResponse`

Re-sends the email verification notification to the currently authenticated user. Calls `sendEmailVerificationNotification()` on the user model and redirects back with a flash message.

## Verification Flow

```
User clicks "Verify Email" (e.g. from ProfileController)
  └─ Server sends signed verification link via email
       └─ User clicks link
            └─ verify() validates signature and marks email as verified
                 └─ Success view displayed with link back to dashboard
```

## Related

- [ProfileController](./profile-controller) — shows the "Verify Email" button when email is unverified
- [Auth\RegisterController](./auth/register-controller) — triggers initial verification email on registration
