---
sidebarPos: 5
sidebarTitle: Migrate Rollback
---

# Migrate Rollback

> Rollback the migrations for a specific module.

## Command Information

- **Signature:** `modularity:migrate:rollback {module}`
- **Category:** Database

## Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `module` | Yes | The module name to roll back (e.g. `Blog`) |

## What It Does

Finds all migration files under `Modules/{Module}/Database/Migrations/`, looks up their batch numbers in the `migrations` table, and rolls back each batch — in reverse order. Only that module's migrations are affected.

## Examples

```bash
php artisan modularity:migrate:rollback Blog
```

## Related

- [migrate](./migrate) — run a module's migrations
- [migrate:refresh](./migrate-refresh) — rollback and re-run
