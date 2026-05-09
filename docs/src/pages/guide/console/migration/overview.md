---
sidebarPos: 7
sidebarTitle: Overview
sidebarGroupTitle: Migration
---

# Migration Commands

Run Modularous-aware migrations. These wrap Laravel's migration commands with awareness of module directories — they scan `modules/*/Database/Migrations/` in addition to the host app's `database/migrations/`.

| Command | Signature | Description |
|---------|-----------|-------------|
| [migrate](./migrate) | `modularous:migrate` | Run all module migrations |
| [migrate:refresh](./migrate-refresh) | `modularous:migrate:refresh` | Rollback and re-run all module migrations |
| [migrate:rollback](./migrate-rollback) | `modularous:migrate:rollback` | Rollback the last batch of module migrations |

## Common Workflows

### Apply new migrations during development

```bash
php artisan modularous:migrate
```

Runs any unmigrated files across all modules. Safe to re-run; already-applied migrations are skipped.

### Reset and re-seed a local database

```bash
php artisan modularous:migrate:refresh
php artisan db:seed
```

Rolls back everything and migrates fresh. **Never use on production** — this drops data. Prefer `migrate:rollback` for targeted undo.

### Revert a mistake

```bash
php artisan modularous:migrate:rollback
```

Rolls back the **last batch** of migrations. Run repeatedly to roll back further batches.

## Related

- [Upgrading](/get-started/upgrading) — migrations play a central role in version upgrades
- [Sync / sync:states](../sync/sync-states) — keep enum-backed state columns in sync after migrations
- [check-collation](../check-collation) — verify database collation settings
