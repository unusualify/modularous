---
sidebarPos: 4
sidebarTitle: Route Disable
---

# Route Disable

> Disable a specific route within a module without removing the module itself.

## Command Information

- **Signature:** `modularous:route:disable {module} {route}`
- **Category:** Module

## Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `module` | Yes | The module name (e.g. `Blog`) |
| `route` | Yes | The route name to disable (e.g. `posts`) |

## What It Does

Looks up the module, checks whether the route is currently enabled, and calls `$module->disableRoute($route)` to deactivate it. If the route is already disabled, a notice is printed but no error is thrown.

Disabled routes are excluded from the router registration on the next request, effectively making them inaccessible without code changes.

## Examples

```bash
php artisan modularous:route:disable Blog posts
php artisan modularous:route:disable Shop api-products
```

## Related

- [route:enable](./route-enable) — re-enable a disabled route
- [route:status](./route-status) — check which routes are enabled or disabled
