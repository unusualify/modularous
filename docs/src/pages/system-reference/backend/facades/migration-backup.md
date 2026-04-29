---
sidebarPos: 7
sidebarTitle: MigrationBackup
---

# MigrationBackup

**Facade**: `Unusualify\Modularity\Facades\MigrationBackup`  
**Accessor**: `migration.backup`  
**Underlying**: `Unusualify\Modularity\Services\MigrationBackupService`

Temporarily backs up and restores table data around destructive migration operations (e.g. dropping and recreating a table). Stores the backup in the cache to survive the migration run.

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `backup` | `(string $table, ?array $columns = null): void` | Reads the table rows (optionally filtered to `$columns`) and saves them to cache |
| `restore` | `(): bool` | Inserts the backed-up rows back into the table; returns `true` on success |
| `getBackup` | `(): array\|null` | Returns the raw backed-up data, or `null` if no backup exists |
| `clearBackup` | `(): void` | Removes the backup from cache |
| `getBackupKey` | `(): string` | Returns the cache key used to store the backup |

## Usage

```php
use Unusualify\Modularity\Facades\MigrationBackup;

// In a migration
public function up()
{
    MigrationBackup::backup('settings');

    Schema::drop('settings');
    Schema::create('settings', function (Blueprint $table) {
        // new schema
    });

    MigrationBackup::restore();
}
```

## Notes

- Intended for migrations that must drop and recreate a table while preserving existing data.
- The backup TTL is long enough to survive a typical migration run but does not persist permanently — call `clearBackup()` if the restore is not needed.
