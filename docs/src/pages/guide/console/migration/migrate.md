---
sidebarPos: 3
sidebarTitle: Migrate
---

# Migrate

> Run the migrations for a specific module.

## Command Information

- **Signature:** `modularous:migrate {module}`
- **Category:** Database

## Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `module` | Yes | The module name whose migrations should run (e.g. `Blog`) |

## What It Does

Resolves the module by name and calls Laravel's `migrate` command pointing at `Modules/{Module}/Database/Migrations/`. Only that module's migrations are run — not the entire application.

## Examples

```bash
php artisan modularous:migrate Blog
php artisan modularous:migrate Shop
```

## Related

- [migrate:refresh](./migrate-refresh) — rollback then re-run a module's migrations
- [migrate:rollback](./migrate-rollback) — rollback a module's last migration batch
