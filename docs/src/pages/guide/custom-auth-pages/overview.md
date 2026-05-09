---
sidebarPos: 1
sidebarTitle: Overview & Architecture
---

# Overview & Architecture

## Two Auth Components

### Package Auth (UeAuth)

- **Location**: `packages/modularous/vue/src/js/components/Auth.vue`
- **Purpose**: Minimal, slot-based layout. No app-specific content.
- **Props**: `slots`, `noDivider`, `noSecondSection`, `logoLightSymbol`, `logoSymbol`
- **Slots**: `description`, `cardTop`, default (form), `bottom`
- **Banner area**: Renders `<slot name="description" />` only when `noSecondSection` is false. No default content.
- **`inheritAttrs: false`**: Custom attributes (e.g. `bannerDescription`) are not applied to the root; they are intended for custom auth components.

### Custom Auth (UeCustomAuth)

- **Location**: `resources/vendor/modularous/js/components/Auth.vue` (published from package)
- **Purpose**: App-specific layouts (split layout, banner, custom branding)
- **Props**: Declare any props you need (e.g. `bannerDescription`, `bannerSubDescription`, `redirectButtonText`, `redirectUrl`)
- **Activation**: Set `auth_pages.component_name` to `ue-custom-auth` in your app config

## Attribute Flow

The auth layout blade passes all attributes to the auth component:

```blade
<{{ $authComponentName }} v-bind='@json($attributes)'>
```

Attributes are built from (in merge order):

1. `auth_pages.layout` — default layout config
2. `layoutPreset` — structural flags (e.g. `noSecondSection`)
3. `auth_pages.attributes` — global attributes for all pages
4. `pages.[pageKey].attributes` — per-page overrides

**Full flexibility**: Any attribute you add in config is passed to the auth component. Custom auth components declare the props they need and receive them automatically.

## Config Sources

| Config | Purpose |
|--------|---------|
| `config/merges/auth_pages.php` | Package defaults (pages, layoutPresets) |
| `modularous/auth_pages.php` | App overrides (attributes, component_name) |
| `config/merges/auth_component.php` | Package UI config (formWidth, dividerText) |
| `modularous/auth_component.php` | App UI overrides |

Use `modularous/auth_pages.php` for deferred loading (when translator is needed for `__()` in attributes).
