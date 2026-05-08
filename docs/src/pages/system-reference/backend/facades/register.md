---
sidebarPos: 16
sidebarTitle: Register
---

# Register

**Facade**: `Unusualify\Modularity\Facades\Register`  
**Accessor**: `auth.register`  
**Underlying**: `Unusualify\Modularity\Brokers\RegisterBrokerManager`

Extends Laravel's `Password` facade to provide an email-verification-based registration flow. Works like `Password::broker()` but for the registration verification pipeline.

## Constants

| Constant | Value description |
|----------|-------------------|
| `VERIFIED_EMAIL_REGISTER` | Registration completed successfully after email verification |
| `VERIFICATION_LINK_SENT` | Verification email sent — user must click the link |
| `ALREADY_REGISTERED` | The email address is already registered |
| `INVALID_VERIFICATION_TOKEN` | The verification token is invalid or expired |
| `VERIFICATION_THROTTLED` | Too many verification attempts — throttled |

## Methods

Inherits the full `Password` facade interface plus:

| Method | Signature | Description |
|--------|-----------|-------------|
| `broker` | `(string\|null $name = null): PasswordBroker` | Resolves the registration broker |
| `sendResetLink` | `(array $credentials, ?Closure $callback = null): string` | Sends a verification/registration link |
| `reset` | `(array $credentials, Closure $callback): mixed` | Completes registration with a valid token |
| `getUser` | `(array $credentials): CanResetPassword\|null` | Resolves the user from credentials |
| `createToken` | `(CanResetPassword $user): string` | Creates a verification token |
| `deleteToken` | `(CanResetPassword $user): void` | Deletes a user's verification token |
| `tokenExists` | `(CanResetPassword $user, string $token): bool` | Checks if a token is valid |

## Usage

```php
use Unusualify\Modularity\Facades\Register;

$status = Register::sendResetLink(['email' => $request->email]);

if ($status === Register::VERIFICATION_LINK_SENT) {
    return back()->with('status', 'Verification email sent.');
}

if ($status === Register::ALREADY_REGISTERED) {
    return back()->withErrors(['email' => 'This email is already registered.']);
}
```

## Notes

- Backed by `RegisterBroker` / `RegisterBrokerManager`, which follow the same interface as Laravel's password broker.
- The `auth.register` container binding is registered by `BaseServiceProvider`.
- See [Brokers →](/system-reference/backend/brokers/overview) for broker-layer internals.
