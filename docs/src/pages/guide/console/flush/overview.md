---
sidebarPos: 9
sidebarTitle: Overview
sidebarGroupTitle: Flush
---

# Flush Commands

Flush runtime state — caches, FilePond temporary uploads, and sessions. These commands complement the [Cache commands](../cache/overview): the Cache group manages Modularous own versioned cache, while Flush clears broader runtime artefacts that accumulate during development and operation.

| Command | Signature | Description |
|---------|-----------|-------------|
| [flush](./flush) | `modularity:flush` | Flush all Modularous caches |
| [flush:filepond](./flush-filepond) | `modularity:flush:filepond` | Delete orphaned FilePond temporary files |
| [flush:sessions](./flush-sessions) | `modularity:flush:sessions` | Clear session data (supports multiple drivers) |

## Common Workflows

### Scheduled housekeeping

Add to your scheduler (`app/Console/Kernel.php` or `routes/console.php`):

```php
Schedule::command('modularity:flush:filepond')->daily();
Schedule::command('modularity:flush:sessions')->weekly();
```

FilePond leaves temporary upload chunks when users abandon forms — clear them daily. Sessions usually self-expire but can accumulate in the `file` driver.

### After clearing data during local dev

```bash
php artisan modularity:flush
php artisan modularity:flush:filepond
```

Resets caches and removes abandoned upload chunks so the UI is in a clean state.

### Force logout everyone

```bash
php artisan modularity:flush:sessions
```

Useful after a security-sensitive change (role permissions, auth config). All active sessions are invalidated; users must log in again.

## Related

- [Cache commands](../cache/overview) — targeted clear/warm/inspect
- [HasFileponds](/guide/generics/file-storage-with-filepond) — what `flush:filepond` works against
