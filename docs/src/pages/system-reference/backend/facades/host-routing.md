---
sidebarPos: 5
sidebarTitle: HostRouting
---

# HostRouting

**Facade**: `Unusualify\Modularous\Facades\HostRouting`  
**Accessor**: `unusualify.hosting`  
**Underlying**: `Unusualify\Modularous\Support\HostRouting`

Provides host-based (subdomain / domain) routing introspection and management. Works alongside `HostableMiddleware` for multi-tenant subdomain scenarios.

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `getHost` | `(): string` | Returns the full current host (e.g. `tenant.app.test`) |
| `getSubdomain` | `(): string` | Returns the subdomain portion |
| `getDomain` | `(): string` | Returns the root domain |
| `getTld` | `(): string` | Returns the TLD |
| `isSubdomain` | `(): bool` | Whether the current request is on a subdomain |
| `isDomain` | `(): bool` | Whether the current request is on the root domain |
| `isTld` | `(): bool` | Whether the current request matches the TLD |
| `getRoutes` | `(): array` | Returns all registered host routes |
| `addRoute` | `(string $host, string $action): void` | Registers a host → action mapping |
| `removeRoute` | `(string $host): void` | Removes a host route mapping |

## Notes

- Used in conjunction with `HostRoutingRegistrar` — `HostRouting` is for introspection and resolving the current host, while `HostRoutingRegistrar` handles registration.
- Pair with `HostableMiddleware` on routes that should be restricted to specific hosts.
