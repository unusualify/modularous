---
sidebarPos: 6
sidebarTitle: HostRoutingRegistrar
---

# HostRoutingRegistrar

**Facade**: `Unusualify\Modularity\Facades\HostRoutingRegistrar`  
**Accessor**: `unusualify.hostRouting`  
**Underlying**: `Unusualify\Modularity\Support\HostRouting`

Manages the registration of host-based routes — adding, removing, and clearing host → action mappings. Companion to `HostRouting` (which handles introspection of the current request).

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `registerRoutes` | `(): void` | Registers all configured host routes with the Laravel router |
| `addRoute` | `(string $host, string $action): void` | Adds a host → action mapping |
| `removeRoute` | `(string $host): void` | Removes a host mapping |
| `getRegisteredRoutes` | `(): array` | Returns all currently registered host routes |
| `clearRegisteredRoutes` | `(): void` | Removes all registered host routes |

## Usage

```php
use Unusualify\Modularity\Facades\HostRoutingRegistrar;

// In a service provider
HostRoutingRegistrar::addRoute('tenant1.app.test', TenantController::class);
HostRoutingRegistrar::registerRoutes();
```

## Notes

- Both `HostRouting` and `HostRoutingRegistrar` resolve to the same underlying `HostRouting` support class but are bound under different container keys (`unusualify.hosting` vs `unusualify.hostRouting`).
- `registerRoutes()` should be called in a service provider's `boot()` method after all host routes have been added.
