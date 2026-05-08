---
sidebarPos: 7
sidebarTitle: db
---

# db

**File**: `src/Helpers/db.php`

Database utility helpers.

## Functions

### `database_exists`

```php
database_exists(string $database, string $connection = null): bool
```

Checks whether a database with the given name exists on the configured connection by attempting a PDO connection directly to that schema.

```php
if (database_exists('my_app_db')) {
    // safe to run migrations
}
```

Uses `try { new PDO(...) }` internally — returns `true` if the connection succeeds and `false` on any `PDOException`. Used by `create:database` and `install` console commands before creating or migrating a schema.
