---
sidebarPos: 1
sidebarTitle: Overview
---

# Helper Functions

Modularous ships 15 PHP helper files, loaded globally via `BaseServiceProvider::registerHelpers()` using a glob over `src/Helpers/*.php`. All functions are wrapped in `function_exists()` guards so host apps can override any helper.

## Files Overview

| File | Functions | Purpose |
|------|-----------|---------|
| [array.php](./array) | 11 | Deep-merge, export, and transform arrays |
| [column.php](./column) | 3 | Table column configuration and translation hydration |
| [component.php](./component) | 5 | Vue modal/component config builders |
| [composer.php](./composer) | 8 | Composer package introspection and env utilities |
| [connector.php](./connector) | 8 | Module connector system entrypoints |
| [db.php](./db) | 1 | Database existence check |
| [format.php](./format) | 45+ | String, class, code-generation, and data helpers |
| [front.php](./front) | 5 | Frontend URL and SVG symbol helpers |
| [i18n.php](./i18n) | 7 | Translation and language data helpers |
| [input.php](./input) | 7 | Form input processing, hydration, and extension |
| [media.php](./media) | 3 | File size formatting and filename sanitization |
| [migrations.php](./migrations) | 8 | Blueprint schema field presets |
| [module.php](./module) | 18 | Module context, config, permission, and debug helpers |
| [router.php](./router) | 3 | Route resolution and URL query helpers |
| [sources.php](./sources) | 14 | Application data for Inertia: navigation, auth, localization |

## How Helpers Are Loaded

```php
// BaseServiceProvider
protected function registerHelpers(): void
{
    foreach (glob(__DIR__ . '/../Helpers/*.php') as $file) {
        require_once $file;
    }
}
```

All helpers are available in controllers, views, Blade templates, and anywhere in the Laravel request lifecycle after the service provider has booted.
