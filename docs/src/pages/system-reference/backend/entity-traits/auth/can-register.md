---
sidebarPos: 1
sidebarTitle: CanRegister
---

# CanRegister

**Namespace**: `Unusualify\Modularous\Entities\Traits\Auth\CanRegister`

Plugs the `User` model into the email-verification registration flow managed by the `Register` facade and `RegisterBrokerManager`. Implements the two contract methods required by `RegisterBroker`.

---

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `getEmailForRegister` | `(): string` | Returns the email address to send the verification link to (`$this->email`) |
| `sendRegisterNotification` | `(string $token, array $parameters = []): void` | Dispatches the `EmailVerification` notification (or a custom class from `modularous.verification_email_class`) |

---

## Configuration

| Config key | Default | Description |
|------------|---------|-------------|
| `modularous.verification_email_class` | `EmailVerification::class` | Notification class used for the verification email |

---

## Usage

This trait is placed on the `User` model. You rarely call these methods directly — they are invoked internally by `RegisterBrokerManager` when `Register::sendResetLink()` is called.

```php
use Unusualify\Modularous\Entities\Traits\Auth\CanRegister;

class User extends Authenticatable
{
    use CanRegister;
}

// Triggered internally by the Register facade:
// Register::sendResetLink(['email' => $user->email]);
// → $user->sendRegisterNotification($token);
```

See the [Register facade →](../../facades/register) for the full registration flow.
