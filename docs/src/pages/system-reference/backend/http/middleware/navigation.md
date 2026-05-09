---
sidebarPos: 11
sidebarTitle: NavigationMiddleware
---

# NavigationMiddleware

**File**: `src/Http/Middleware/NavigationMiddleware.php`  
**Alias**: `modularous.navigation`  
**Part of**: `modularous.core` group

Shares the resolved navigation config with the Blade layout views on every request.

## What It Does

Registers a view composer for `modularous::layouts.*` and `translation::layout`:

```php
view()->composer([
    'modularous::layouts.*',
    'translation::layout',
], function ($view) {
    $view->with('navigation', get_modularous_navigation_config());
});
```

`get_modularous_navigation_config()` reads the `modularous.navigation` config key (populated at request time by `LoadLocalizedConfig`) and returns the fully resolved navigation array used to render the sidebar and top navigation.

## When It Runs

`NavigationMiddleware` is part of the `modularous.core` group, so it runs on every Modularous route — both public web routes and authenticated panel routes. The view composer is registered lazily; it only executes when a matching layout view is actually rendered.

## Notes

- Navigation data is shared only with Blade views. For Inertia pages, navigation is handled inside `HandleInertiaRequests` via `storeData.config`.
- The navigation config is loaded at request time (after `LoadLocalizedConfig` has merged app overrides), so per-app navigation customisations in `{base_path}/modularous/navigation.php` are reflected without a config cache rebuild.
