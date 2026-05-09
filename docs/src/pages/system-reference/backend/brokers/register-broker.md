---
sidebarPos: 2
sidebarTitle: RegisterBroker
---

# RegisterBroker

**File**: `src/Brokers/RegisterBroker.php`  
**Namespace**: `Unusualify\Modularous\Brokers`  
**Extends**: `Illuminate\Auth\Passwords\PasswordBroker`  
**Implements**: `Unusualify\Modularous\Contracts\RegisterBroker`

`RegisterBroker` performs the actual registration verification workflow: sending verification links, validating email+token pairs, and finalizing registration callbacks.

## Constructor Dependencies

| Dependency | Purpose |
|------------|---------|
| `TokenRepositoryInterface $tokens` | Create/check throttled tokens |
| `UserProvider $users` | User resolution (inherited behavior) |
| `ConnectionInterface $connection` | Direct DB reads/deletes for token rows |
| `array $config` | Token table/expiry settings |

## Core Methods

| Method | Purpose |
|--------|---------|
| `sendVerificationLink(array $credentials, ?Closure $callback = null)` | Sends verification token for an unregistered email |
| `register(array $credentials, Closure $callback)` | Validates token and executes registration callback |
| `validateRegister(array $credentials)` | Returns `VERIFICATION_SUCCESS` or error status constants |
| `emailIsRegistered($email)` | Checks users table for existing email |
| `emailTokenExists($email, $token)` | Verifies token record presence, expiry, and hash |
| `deleteToken($email)` | Deletes token row after successful register |

## Return Statuses

The class returns facade-compatible statuses such as:

- `ALREADY_REGISTERED`
- `RESET_THROTTLED`
- `INVALID_VERIFICATION_TOKEN`
- `VERIFICATION_SUCCESS`
- `VERIFICATION_LINK_SENT`

## Notes

- `sendVerificationLink(...)` creates a temporary `User` with only email to reuse token repository APIs.
- If callback is provided to `sendVerificationLink`, callback output can override default success response.
- Token expiry is computed as `created_at + (config['expire'] * 60 seconds)`.

## Related

- [RegisterBrokerManager](./register-broker-manager)
- [Facades · Register](/system-reference/backend/facades/register)
