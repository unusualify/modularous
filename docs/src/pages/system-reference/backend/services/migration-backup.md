---
sidebarPos: 17
sidebarTitle: MigrationBackup
---

# MigrationBackup

**File**: `src/Services/MigrationBackup.php`  
**Facade**: `Unusualify\Modularity\Facades\MigrationBackup`

Provides data-safe migration helpers that snapshot table data and schema to the Laravel cache before running destructive migrations, then restore rows if a rollback is needed.

Supports **MySQL**, **PostgreSQL**, and **SQLite**. All cache keys are namespaced by the calling migration's filename, so concurrent migrations never collide.

## How It Works

1. **Before migration** — call `backup()`. The service snapshots the table's current rows and schema (column types, nullability, defaults, foreign keys) to the Laravel cache.
2. **Run migration** — execute your `Schema::` changes normally.
3. **On rollback** — call `restore()`. The service re-inserts backed-up rows, adapting them to the current schema (new columns get type-appropriate defaults; removed columns are silently dropped).
4. **After rollback** — call `clearBackup()` to remove the cache entries.

## Key Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `backup` | `backup(string $table, ?array $columns, bool $includeRelated): void` | Snapshot a table to cache. When `$includeRelated` is `true` (default), FK-referenced tables are also snapshotted. |
| `restore` | `restore(?string $table): bool` | Restore rows from the snapshot. Pass `null` to restore every table backed up by this migration. Returns `true` on success. |
| `clearBackup` | `clearBackup(?string $table): void` | Remove cached snapshots. Pass `null` to clear all snapshots for this migration. |
| `getBackup` | `getBackup(?string $table): ?array` | Inspect the raw snapshot data stored in cache. |
| `getSchemaHistory` | `getSchemaHistory(?string $table): array` | Return a log of column additions/removals/modifications detected during restore. |

## Usage in Migrations

```php
use Unusualify\Modularity\Services\MigrationBackup;

class AddSkuToProductsTable extends Migration
{
    public function up(): void
    {
        $backup = app(MigrationBackup::class);
        $backup->backup('products');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('legacy_code');
            $table->string('sku')->unique();
        });
    }

    public function down(): void
    {
        $backup = app(MigrationBackup::class);
        $backup->restore('products');
        $backup->clearBackup('products');
    }
}
```

## Foreign Key Handling

During restore, foreign key constraints are temporarily disabled:

| Driver | Statement |
|--------|-----------|
| MySQL | `SET FOREIGN_KEY_CHECKS=0` |
| PostgreSQL | `SET CONSTRAINTS ALL DEFERRED` |
| SQLite | `PRAGMA foreign_keys=OFF` |

Constraints are re-enabled in a `finally` block, even if restore fails.

## Schema Adaptation

When restoring rows after a schema change, the service:

- **Removes** columns that no longer exist from the row data before inserting
- **Fills** new columns with a type-appropriate default:

| Column type | Default |
|-------------|---------|
| `varchar`, `text`, `char` | `''` |
| `int`, `bigint`, `smallint` | `0` |
| `decimal`, `float`, `double` | `0.0` |
| `boolean`, `tinyint(1)` | `false` |
| `datetime`, `timestamp`, `date`, `json` | `null` |

## Cache Key Format

```
migration_backup_{migration-file-slug}_{table-slug}
```

All backup keys for a migration are tracked under `migration_backups_{migration-file-slug}`, which is also cleared by `clearBackup()`.
