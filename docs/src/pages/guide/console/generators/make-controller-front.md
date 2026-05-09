---
sidebarPos: 15
sidebarTitle: Make Controller Front
---

# Make Controller Front

> Generate an Inertia frontend controller for a module.

## Command Information

- **Signature:** `modularous:make:controller:front {module} {name}`
- **Category:** Generators

## Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `module` | Yes | The module the controller belongs to (e.g. `Blog`) |
| `name` | Yes | Controller class name (e.g. `Post` → generates `PostFrontController`) |

## What It Does

Creates a frontend controller stub that uses `Inertia::render()` to return Vue page components to the browser. Use this for public-facing routes that need server-side data passed to Vue via Inertia props.

## Examples

```bash
php artisan modularous:make:controller:front Blog Post
# → Modules/Blog/Http/Controllers/PostFrontController.php

php artisan modularous:make:controller:front Shop Product
```

## Related

- [make:controller](./make-controller) — standard admin CRUD controller
- [make:controller:api](./make-controller-api) — API-only controller
