---
sidebarPos: 9
sidebarTitle: TelescopeServiceProvider
---

# TelescopeServiceProvider

**Class**: `Unusualify\Modularous\Providers\TelescopeServiceProvider`  
**Source**: `src/Providers/TelescopeServiceProvider.php`  
**Extends**: `Laravel\Telescope\TelescopeApplicationServiceProvider`

Configures Laravel Telescope access control and entry filtering. Registered automatically by [`BaseServiceProvider`](./base-service-provider) — not listed in `ModularousProvider`'s provider array.

## `boot()`

1. Calls `$this->gate()` to define the `viewTelescope` gate
2. Sets the Telescope auth callback:

```php
Telescope::auth(function ($request) {
    return app()->environment('local') ||
        Gate::check('viewTelescope', [$request->user()]);
});
```

Telescope is accessible to everyone in `local` environments. In other environments, only users who pass the `viewTelescope` gate can access it.

## `register()`

### Entry filtering

```php
Telescope::filter(function (IncomingEntry $entry) use ($isLocal) {
    return $isLocal ||
           $entry->isReportableException() ||
           $entry->isFailedRequest() ||
           $entry->isFailedJob() ||
           $entry->isScheduledTask() ||
           $entry->isSlowQuery() ||
           $entry->hasMonitoredTag();
});
```

In `local`, every entry is recorded. In production/staging, only the following entry types are stored:

| Entry type | Method |
|------------|--------|
| Reportable exceptions | `isReportableException()` |
| Failed HTTP requests | `isFailedRequest()` |
| Failed queued jobs | `isFailedJob()` |
| Scheduled task runs | `isScheduledTask()` |
| Slow database queries | `isSlowQuery()` |
| Entries with a monitored tag | `hasMonitoredTag()` |

### Sensitive data filtering (non-local)

Removes the following from recorded entries:

| Type | Filtered values |
|------|----------------|
| Request parameters | `_token` |
| Request headers | `cookie`, `x-csrf-token`, `x-xsrf-token` |

## `gate()`

Defines the `viewTelescope` gate. By default the allowed-emails list is empty — add emails to grant non-local access:

```php
Gate::define('viewTelescope', function ($user) {
    return in_array($user->email, [
        'admin@example.com',
    ]);
});
```
