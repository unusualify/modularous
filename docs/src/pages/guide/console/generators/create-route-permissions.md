---
sidebarPos: 7
sidebarTitle: Create Route Permissions
---

# Create Route Permissions

> Generate Spatie Permission records for all actions of a module route.

## Command Information

- **Signature:** `modularity:make:route:permissions [--route[=ROUTE]] <route>`
- **Aliases:** `modularity:create:route:permissions`
- **Category:** Generators

## Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `route` | Yes | The module route name to generate permissions for (e.g. `products`) |

## Options

| Option | Default | Description |
|--------|---------|-------------|
| `--route` | `null` | Optional override for the route name (if different from the argument) |

## What It Does

Resolves the given route name via `RouteGenerator` and calls `createRoutePermissions()` to seed Spatie `permissions` records for every CRUD action defined on that route (index, create, store, show, edit, update, destroy, etc.).

Run this after adding a new module or route to ensure the permission records exist before assigning them to roles.

## Examples

```bash
# Generate permissions for the "products" route
php artisan modularity:make:route:permissions products

# Using the option form
php artisan modularity:make:route:permissions --route=products products
```

## Related

- [make:route](/guide/console/generators/make-route) — add a route to an existing module
- [Spatie Laravel-Permission](https://spatie.be/docs/laravel-permission) — the underlying permissions library
