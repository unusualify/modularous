---
sidebarPos: 17
sidebarTitle: Refresh
---

# Refresh

> Republish Modularous frontend assets to `public/vendor/modularity` and clear view/application caches.

## Command Information

- **Signature:** `modularity:refresh`
- **Category:** Module

## What It Does

1. Deletes `public/vendor/modularity` entirely.
2. Runs `vendor:publish --provider=LaravelServiceProvider --tag=modularity-assets --force` to copy fresh assets.
3. Calls `cache:clear` and `view:clear`.

Run this after upgrading the Modularous package to ensure the browser receives the updated JS/CSS files.

## Example

```bash
php artisan modularity:refresh
```

## Related

- [build](./assets/build) — rebuild custom Vue assets
- [get:version](./get-version) — confirm the installed Modularous version
