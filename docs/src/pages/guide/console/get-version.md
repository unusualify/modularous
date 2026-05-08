---
sidebarPos: 2
sidebarTitle: Get Version
---

# Get Version

> Print the installed version of any Composer package.

## Command Information

- **Signature:** `modularity:get:version [--p|package=]`
- **Alias:** `mod:g:ver`
- **Category:** Module

## Options

| Option | Description |
|--------|-------------|
| `--package=` / `-p` | The Composer package name to look up (e.g. `unusualify/modularity`) |

## What It Does

Calls `get_package_version($package)` and prints the result. Useful for quickly confirming which version of Modularous or any dependency is active without opening `composer.lock`.

## Examples

```bash
php artisan modularity:get:version --package=unusualify/modularity
php artisan mod:g:ver -p laravel/framework
```

## Related

- [refresh](./refresh) — republish frontend assets after an upgrade
