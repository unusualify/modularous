---
sidebarPos: 2
sidebarTitle: Update Laravel Configs
---

# Update Laravel Configs

> Patch host-application config files with the Modularous authentication guards, providers, and optional module/translation settings.

::: warning Hidden command
This command has `$hidden = true` and does not appear in `php artisan list`. It is run automatically during `modularous:install` and when upgrading between major versions.
:::

## Command Information

- **Signature:** `modularous:update:laravel:configs`
- **Category:** Update

## What It Does

The command applies a series of targeted patches to the host application's published config files:

1. **`config/auth.php`** — adds the Modularous guard and user provider definitions so that `Auth::guard('modularous')` resolves correctly.
2. **`config/modules.php`** _(if present)_ — merges Modularous module discovery paths.
3. **`config/translation.php`** _(if present)_ — registers Modularous translation file groups.

All patches are idempotent — running the command twice will not duplicate entries.

## Example

```bash
php artisan modularous:update:laravel:configs
```

This is typically run as part of the post-install / post-update workflow:

```bash
php artisan modularous:install
# or, after a composer update:
php artisan modularous:update:laravel:configs
```

## Related

- [Setup commands](/guide/console/setup/overview) — full installation workflow
