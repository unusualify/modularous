---
sidebarPos: 4
sidebarTitle: TokenRepositoryInterface
---

# TokenRepositoryInterface

**File**: `src/Brokers/TokenRepositoryInterface.php`  
**Namespace**: `Unusualify\Modularous\Brokers`  
**Extends**: `Illuminate\Auth\Passwords\TokenRepositoryInterface`

Modularous token repository contract used by registration broker flow. It extends Laravel's token repository interface with email-oriented method signatures.

## Added Contract Methods

| Method | Signature | Purpose |
|--------|-----------|---------|
| `recentlyCreatedToken` | `($email)` | Checks throttle state for an email |
| `create` | `($email)` | Creates token for an email |

These signatures are used by `RegisterBroker`, which operates on email-centric registration flow instead of only `CanResetPassword` model inputs.

## Related

- [RegisterBroker](./register-broker)
- [RegisterBrokerManager](./register-broker-manager)
