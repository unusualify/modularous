---
sidebarPos: 13
sidebarTitle: make:operation
---

# make:operation

> Create a one-time operation file

**Signature**: `modularity:make:operation`

**Aliases**: `modularity:create:operation`, `mod:c:operation`, `modularity:operations:make`

**Category**: Make

---

## Description

Generates a timestamped one-time operation file for [timokoerber/laravel-one-time-operations](https://github.com/timokoerber/laravel-one-time-operations). Operation files are run exactly once via the operations pipeline. Use `--self` to create a Modularous-internal operation (tagged `modularity`) in the vendor `operations/` folder.

---

## Usage

```
modularity:make:operation [options] <name>
```

### Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `name` | yes | Operation name (snake-cased in filename) |

### Options

| Option | Short | Description |
|--------|-------|-------------|
| `--self` | | Write to Modularous vendor `operations/` and tag as `modularity` |
| `--path=` | | Custom output directory (default: `config('one-time-operations.directory')`) |
| `--tag=` | `-t` | Tag string for the operation |
| `--async` | | Mark the operation as asynchronous |
| `--queue=` | | Queue name (default: `default`) |

---

## Examples

### Standard one-time operation

```bash
php artisan modularity:make:operation SeedNewPermissions
# → operations/2026_04_28_120000_seed_new_permissions_operation.php
```

### Tagged async operation on a custom queue

```bash
php artisan modularity:make:operation BackfillPostSlugs \
    --tag=backfill \
    --async \
    --queue=high-priority
```

### Internal Modularous operation

```bash
php artisan modularity:make:operation AddDefaultSettings --self
# → src/operations/2026_04_28_120000_modularity_add_default_settings_operation.php
```

---

## Output

`{path}/{timestamp}_{name}_operation.php`

**Stub**: `operation.stub`

---

## See also

- [Operations: process-operations](/guide/console/operations/process-operations) — run all pending operations
- [System Reference](/system-reference/backend/console/make#makeoperationcommand)
