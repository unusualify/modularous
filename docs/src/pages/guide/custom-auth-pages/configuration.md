---
sidebarPos: 3
sidebarTitle: Configuration
---

# Configuration

## auth_pages

Primary config for auth pages. Override in `modularous/auth_pages.php` or merge into `config/modularous.php`.

### Top-Level Keys

| Key | Type | Description |
|-----|------|-------------|
| `component_name` | string | Auth component to use: `ue-auth` (package default) or `ue-custom-auth` |
| `layout` | array | Default layout attributes (e.g. `logoSymbol`, `logoLightSymbol`) |
| `attributes` | array | Global attributes passed to all auth pages |
| `pages` | array | Per-page definitions (login, register, forgot_password, etc.) |
| `layoutPresets` | array | Reusable structural presets (banner, minimal) |

### Example: modularous/auth_pages.php

```php
<?php

return [
    'component_name' => 'ue-custom-auth',
    'attributes' => [
        'bannerDescription' => __('authentication.banner-description'),
        'bannerSubDescription' => __('authentication.banner-sub-description'),
        'redirectButtonText' => __('authentication.redirect-button-text'),
    ],
];
```

### Deferred Loading

When using `__()` or `___()` in attributes, load auth config via defers so the translator is available:

- `config/defers/auth_pages.php` — merged by `LoadLocalizedConfig` middleware
- Or use `modularous/auth_pages.php` which is typically loaded after translator

## auth_component

UI and styling config. Passed to Vue via `window.__MODULAROUS_AUTH_CONFIG__`.

| Key | Type | Description |
|-----|------|-------------|
| `formWidth` | array | Form width by breakpoint (`xs`, `sm`, `md`, `lg`, `xl`, `xxl`) |
| `layout` | array | Column classes for custom auth layouts |
| `banner` | array | Banner section classes (titleClass, buttonClass) |
| `dividerText` | string | Text between form and bottom slots (e.g. "or") |
| `useLegacy` | bool | When true, use UeCustomAuth (legacy design) |

### Example: auth_component formWidth

```php
'formWidth' => [
    'xs' => '85vw',
    'sm' => '450px',
    'md' => '450px',
    'lg' => '500px',
    'xl' => '600px',
    'xxl' => 700,
],
```
