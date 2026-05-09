---
sidebarPos: 5
sidebarTitle: Route Enable
---

# Route Enable

> Re-enable a previously disabled route within a module.

## Command Information

- **Signature:** `modularous:route:enable {module} {route}`
- **Category:** Module

## Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `module` | Yes | The module name (e.g. `Blog`) |
| `route` | Yes | The route name to enable (e.g. `posts`) |

## What It Does

Looks up the module, checks whether the route is currently disabled, and calls `$module->enableRoute($route)` to activate it. If the route is already enabled, a notice is printed and no change is made.

Once enabled, the route is registered with the router on the next request.

## Examples

```bash
php artisan modularous:route:enable Blog posts
php artisan modularous:route:enable Shop api-products
```

## Related

- [route:disable](./route-disable) — disable a route
- [route:status](./route-status) — inspect route enable/disable state
