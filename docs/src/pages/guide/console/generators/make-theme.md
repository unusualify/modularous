---
sidebarPos: 27
sidebarTitle: Make Theme
---

# Make Theme

> Generalise (export) a local custom theme into the Modularous theme index so it can be referenced globally.

## Command Information

- **Signature:** `modularity:make:theme {name} [--force]`
- **Category:** Generators

## Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `name` | Yes | Name of the custom theme to generalise (must already exist under `resources/vendor/modularity/themes/{name}/`) |

## Options

| Option | Description |
|--------|-------------|
| `--force` | Overwrite existing theme export entries |

## What It Does

Reads the theme's JS entry point at `resources/vendor/modularity/themes/{name}/{name}.js` and appends a named export to the Modularous themes index file:

```js
// vue/src/js/config/themes/index.js
export { default as corporate } from './corporate'
```

This makes the theme available to Modularous theme system without manual edits to the index. Run after creating a new theme folder with [create:theme](./create-theme).

## Examples

```bash
php artisan modularity:make:theme corporate
php artisan modularity:make:theme dark-mode --force
```

## Related

- [create:theme](./create-theme) — create the theme folder structure first
