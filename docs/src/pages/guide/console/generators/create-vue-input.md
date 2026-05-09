---
sidebarPos: 11
sidebarTitle: Create Vue Input
---

# Create Vue Input

> Generate a Vue input component stub in the Modularous vendor inputs directory.

## Command Information

- **Signature:** `modularous:make:vue:input {name}`
- **Aliases:** `modularous:create:vue:input`, `mod:c:vue:input`
- **Category:** Generators

## Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `name` | Yes | Component name (e.g. `ColorPicker` → generates `ColorPicker.vue` registered as `v-input-color-picker`) |

## What It Does

Creates a `.vue` file from the `input-component.vue` stub in:

```
vendor/modularous/vue/src/js/components/inputs/{StudlyName}.vue
```

The component is registered in the input registry with a kebab-case tag name prefixed `v-input-`. If the file already exists the command skips creation and reports a warning.

## Examples

```bash
php artisan modularous:make:vue:input ColorPicker
# → creates ColorPicker.vue, registered as v-input-color-picker

php artisan mod:c:vue:input RatingStars
```

## Related

- [create:input-hydrate](./create-input-hydrate) — generate the matching server-side Hydrate class
- [Input Registry](/system-reference/frontend/overview#input-registry) — how custom input types are registered
