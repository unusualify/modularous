---
sidebarPos: 3
sidebarTitle: RegisterBrokerManager
---

# RegisterBrokerManager

**File**: `src/Brokers/RegisterBrokerManager.php`  
**Namespace**: `Unusualify\Modularity\Brokers`  
**Extends**: `Illuminate\Auth\Passwords\PasswordBrokerManager`

`RegisterBrokerManager` is the broker factory behind the `auth.register` binding. It resolves named registration brokers and builds `RegisterBroker` instances with the configured provider/connection/token settings.

## Key Behavior

| Method | Behavior |
|--------|----------|
| `resolve($name)` | Reads broker config, throws `InvalidArgumentException` if missing, returns `RegisterBroker` |
| `getDefaultDriver()` | Returns `register_verified_users` as the default broker name |

## Resolution Details

When resolving a broker, the manager composes:

- token repository from password broker config
- user provider via `auth.createUserProvider(...)`
- DB connection from broker config (`connection`)
- full config array passed into `RegisterBroker`

## Failure Mode

If requested broker name is undefined, `resolve($name)` throws:

`InvalidArgumentException("Email verification broker [{$name}] is not defined.")`

## Related

- [RegisterBroker](./register-broker)
- [Facades · Register](/system-reference/backend/facades/register)
- [Providers · BaseServiceProvider](/system-reference/backend/providers/base-service-provider)
