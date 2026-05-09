---
sidebarPos: 9
sidebarTitle: LoadLocalizedConfig
---

# LoadLocalizedConfig

**File**: `src/Http/Middleware/LoadLocalizedConfig.php`  
**Alias**: `modularous.loadLocalizedConfig`  
**Part of**: `modularous.core` group

Merges deferred and application-level config files into the live `modularous.*` config at request time. This allows config to be split across multiple files and overridden per-application without touching the published package config.

## What It Does

### 1. Merge vendor deferred configs

Scans `{vendor_path}/config/defers/*.php` and merges each file into `modularous.{filename}`:

```php
foreach (glob(Modularous::getVendorPath('config/defers/*.php')) as $path) {
    mergeConfigFrom($path, 'modularous.{filename}');
}
```

### 2. Merge application-level overrides

Scans `{base_path}/modularous/*.php` and deep-merges each file into the corresponding `modularous.{filename}` config key using `array_merge_recursive_preserve()`:

```
{project_root}/modularous/
  ├── navigation.php    → modularous.navigation
  ├── ui_settings.php  → modularous.ui_settings
  └── permissions.php  → modularous.permissions
```

### 3. Navigation fallback (deprecated)

If no `modularous/navigation.php` file exists in the app, the middleware falls back to merging `modularous-navigation` config (the legacy approach). This behaviour is deprecated since `10.0.0` — create a `modularous/navigation.php` file instead.

## When to Use

This middleware runs on every request in `modularous.core`. It is designed to be lightweight — only reads files that are already loaded by PHP's opcode cache.

Application config overrides placed in `{base_path}/modularous/` are picked up without needing to republish or re-cache the full config, making it suitable for per-tenant or per-environment customisation.

## Notes

- Only files where `config('modularous.{filename}')` already has a value are merged. Files that introduce brand-new keys are skipped (use a service provider `mergeConfigFrom` for those).
- `array_merge_recursive_preserve` is used instead of `array_merge_recursive` to avoid duplicate array values when merging nested config arrays.
