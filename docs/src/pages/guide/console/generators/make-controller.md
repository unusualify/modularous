---
sidebarPos: 13
sidebarTitle: Make Controller
---

# Make Controller

> Generate a standard CRUD controller with repository injection for a module.

## Command Information

- **Signature:** `modularity:make:controller {module} {name}`
- **Category:** Generators

## Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `module` | Yes | The module the controller belongs to (e.g. `Blog`) |
| `name` | Yes | Controller class name (e.g. `Post` → generates `PostController`) |

## What It Does

Creates a `{Name}Controller.php` stub inside the module's `Http/Controllers/` directory. The generated controller extends Modularous base controller and is wired to accept the matching repository via constructor injection.

## Examples

```bash
php artisan modularity:make:controller Blog Post
# → Modules/Blog/Http/Controllers/PostController.php

php artisan modularity:make:controller Shop Product
```

## Related

- [make:controller:api](./make-controller-api) — API-only controller variant
- [make:controller:front](./make-controller-front) — Inertia frontend controller variant
- [make:repository](./make-repository) — generate the matching repository
