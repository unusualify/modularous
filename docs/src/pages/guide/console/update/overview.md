---
sidebarPos: 15
sidebarTitle: Overview
sidebarGroupTitle: Update
---

# Update Commands

Update commands patch host-application configuration files to align with Modularous requirements during installation or version upgrades. They perform **surgical, reversible edits** to keep your `config/*.php` files compatible — they don't overwrite your customisations.

::: warning Internal commands
All Update commands have `$hidden = true` and are not listed in `php artisan list`. They are invoked automatically by `modularity:install` and by the upgrade guide, but you can run them manually when something drifts.
:::

| Command | Description |
|---------|-------------|
| [modularity:update:laravel:configs](/guide/console/update/update-laravel-configs) | Patch `config/auth.php` and optional config files for Modularous guards |

## When to Run These Manually

| Symptom | Likely fix |
|---------|------------|
| Login page rejects valid credentials with "guard not defined" | `php artisan modularity:update:laravel:configs` |
| Fresh clone missing Modularous guards in `config/auth.php` | `php artisan modularity:update:laravel:configs` |
| Upgraded Modularous and release notes mention new guards / providers | `php artisan modularity:update:laravel:configs` |

Always diff `config/auth.php` against git before committing after running an Update command — the patch is transparent, but you should understand what changed.

## Related

- [Upgrading](/get-started/upgrading) — the primary context for Update commands
- [Setup / install](../setup/install) — invokes Update commands automatically on first install
