---
sidebarPos: 2
sidebarTitle: Flush
---

# Flush

> Flush all Modularous caches and display current cache versions.

::: warning Hidden command
This command has `$hidden = true` and does not appear in `php artisan list`.
:::

## Command Information

- **Signature:** `modularous:flush`
- **Category:** Flush

## What It Does

Calls `Modularous::clearCache()` to wipe all Modularous cache entries, then runs `modularous:cache:versions` to print the refreshed version numbers — a quick confirmation that the flush succeeded.

## Example

```bash
php artisan modularous:flush
```

## Related

- [flush:filepond](./flush-filepond) — delete orphaned FilePond temporary uploads
- [flush:sessions](./flush-sessions) — clear session data
- [cache:clear](/guide/console/cache/cache-clear) — fine-grained cache clearing by module/type
