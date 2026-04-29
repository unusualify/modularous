---
sidebarPos: 14
sidebarTitle: Make Controller API
---

# Make Controller API

> Generate an API controller with repository injection for a module.

## Command Information

- **Signature:** `modularity:make:controller:api {module} {name}`
- **Category:** Generators

## Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `module` | Yes | The module the controller belongs to (e.g. `Blog`) |
| `name` | Yes | Controller class name (e.g. `Post` → generates `PostApiController`) |

## What It Does

Creates an API controller stub that returns JSON responses. Use this when you need a dedicated REST API endpoint for a module resource, separate from the admin panel controller.

## Examples

```bash
php artisan modularity:make:controller:api Blog Post
# → Modules/Blog/Http/Controllers/PostApiController.php

php artisan modularity:make:controller:api Shop Product
```

## Related

- [make:controller](./make-controller) — standard admin CRUD controller
- [make:controller:front](./make-controller-front) — Inertia frontend controller
