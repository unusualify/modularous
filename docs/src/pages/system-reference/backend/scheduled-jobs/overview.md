---
sidebarPos: 1
sidebarTitle: Overview
sidebarGroupTitle: Scheduled Jobs
---

# Scheduled Jobs

Modularous registers its own recurring jobs directly against Laravel's `Schedule` inside `BaseServiceProvider`. No `Console\Kernel.php` is needed in the host application — the jobs run as long as the standard Laravel scheduler is active.

## Registered Jobs

| Command | Class | Frequency | Purpose |
|---------|-------|-----------|---------|
| `modularous:fileponds:scheduler` | [FilepondsScheduler](./fileponds-scheduler) | Daily | Delete expired temporary filepond uploads and orphaned storage folders |
| `modularous:scheduler:chatable` | [ChatableScheduler](./chatable-scheduler) | Every minute | Send unread-chat notifications for all `Chatable` models |
| `telescope:prune` | Laravel Telescope | Daily | Prune Telescope entries older than 168 hours (7 days) |

## How They Are Registered

The schedule is wired up in `BaseServiceProvider::boot()` using `callAfterResolving` so the bindings are resolved after the container is fully booted:

```php
$this->callAfterResolving(Schedule::class, function (Schedule $schedule) {

    $schedule->command('modularous:fileponds:scheduler --days=7')
        ->daily();

    $schedule->command('telescope:prune --hours=168')
        ->daily()
        ->appendOutputTo(storage_path('logs/scheduler.log'));

    $schedule->command('modularous:scheduler:chatable')
        ->everyMinute();
});
```

Both scheduler classes (`ChatableScheduler`, `FilepondsScheduler`) are discovered automatically from `src/Schedulers/*.php` via `CommandDiscovery` and registered as Artisan commands, so they can also be run manually:

```bash
php artisan modularous:fileponds:scheduler
php artisan modularous:scheduler:chatable
```

## Prerequisites

The host application must have the Laravel scheduler running. Add one cron entry to the server:

```cron
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Logging

Both schedulers write to the `scheduler` log channel on error or completion. Configure this channel in `config/logging.php`:

```php
'scheduler' => [
    'driver' => 'daily',
    'path'   => storage_path('logs/scheduler.log'),
    'level'  => 'debug',
    'days'   => 14,
],
```

Telescope pruning appends its output directly to `storage/logs/scheduler.log` via `->appendOutputTo(...)`.
