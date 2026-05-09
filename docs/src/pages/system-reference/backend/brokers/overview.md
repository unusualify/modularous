---
sidebarPos: 1
sidebarTitle: Overview
sidebarGroupTitle: Brokers
---

# Brokers

**Directory**: `src/Brokers/`  
**Namespace**: `Unusualify\Modularous\Brokers`

The broker layer powers Modularous registration verification flow behind the [`Register` facade](/system-reference/backend/facades/register). It mirrors Laravel's password broker design, but adapts it for email-verification-based registration.

## Classes

| Class | Role | Page |
|-------|------|------|
| `RegisterBroker` | Executes verification-link and registration-token operations | [RegisterBroker →](./register-broker) |
| `RegisterBrokerManager` | Resolves named broker instances and selects default broker | [RegisterBrokerManager →](./register-broker-manager) |
| `TokenRepositoryInterface` | Contract extension for email-based token repository methods | [TokenRepositoryInterface →](./token-repository-interface) |

## Flow

1. Controller/facade calls `Register::broker()` or `Register::sendResetLink(...)`.
2. `RegisterBrokerManager` resolves a `RegisterBroker` for the configured broker name.
3. `RegisterBroker` creates/validates/deletes verification tokens.
4. On successful validation, registration callback is executed.

## Related

- [Facades · Register](/system-reference/backend/facades/register)
- [HTTP · Auth · PreRegisterController](/system-reference/backend/http/controllers/auth/pre-register-controller)
- [HTTP · Auth · CompleteRegisterController](/system-reference/backend/http/controllers/auth/complete-register-controller)
