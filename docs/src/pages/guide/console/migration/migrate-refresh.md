---
sidebarPos: 4
sidebarTitle: Migrate Refresh
---

# Migrate Refresh

> Rollback then re-run all migrations for a specific module.

## Command Information

- **Signature:** `modularity:migrate:refresh {module}`
- **Category:** Database

## Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `module` | Yes | The module name to refresh (e.g. `Blog`) |

## What It Does

Calls `modularity:migrate:rollback` followed by `modularity:migrate` for the given module. Use during development to reset a module's schema and replay its migrations cleanly.

::: warning
This drops and recreates the module's tables. Any data in those tables will be lost.
:::

## Examples

```bash
php artisan modularity:migrate:refresh Blog
```

## Related

- [migrate](./migrate) — run a module's migrations
- [migrate:rollback](./migrate-rollback) — rollback only
