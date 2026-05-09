---
sidebarPos: 3
sidebarTitle: FilepondsScheduler
---

# FilepondsScheduler

`Unusualify\Modularous\Schedulers\FilepondsScheduler`

Artisan command that cleans up abandoned filepond uploads. It runs **daily** via the Modularous scheduler registration and can also be triggered manually at any time.

## Signature

```
modularous:fileponds:scheduler {--days=7}
```

| Option | Default | Description |
|--------|---------|-------------|
| `--days` | `7` | Delete temporary filepond records created more than this many days ago |

## What It Does

The command calls two `FilepondManager` methods in sequence:

### 1. `Filepond::clearTemporaryFiles($days)`

Removes expired temporary uploads — filepond records in the `temporary_fileonds` table whose `created_at` is older than `$days` days.

Steps performed:
1. Query `TemporaryFilepond` records where `created_at < now()->subDays($days)`
2. For each record, delete its storage directory (`{tmp_path}/{folder_name}/`)
3. Delete the database records
4. Scan the remaining tmp directories and remove any empty folders not referenced by a `TemporaryFilepond` record

Returns the deleted collection, whose count is written to the log.

### 2. `Filepond::clearFolders()`

Removes orphaned storage folders from the main filepond file path — directories that exist on disk but are not referenced by any `Filepond` UUID in the database and are not the tmp path itself.

Steps performed:
1. List all directories under `$file_path`
2. Build an exclusion list: the tmp path + all UUIDs from `Filepond::all()`
3. Delete any directory in the diff that still contains files

## Schedule

Registered as `->daily()` in `BaseServiceProvider`. The default `--days=7` retention period is baked into the schedule entry:

```php
$schedule->command('modularous:fileponds:scheduler --days=7')->daily();
```

## Manual Usage

```bash
# Use the default 7-day retention
php artisan modularous:fileponds:scheduler

# Extend retention to 30 days
php artisan modularous:fileponds:scheduler --days=30

# Aggressive cleanup: delete anything older than 1 day
php artisan modularous:fileponds:scheduler --days=1
```

## Logging

On completion, writes an info entry to the `scheduler` log channel:

```
Modularous: Deleted {N} expired temporary fileponds in last {days} days
```

No error handling wraps this command — exceptions propagate to the scheduler and are captured by Laravel's default scheduler error handling.

## Related

- `flush:filepond` — the manual flush command that also clears filepond data on demand.
- [FilepondManager](/system-reference/backend/services/uploader/overview) — the service class implementing `clearTemporaryFiles` and `clearFolders`.
