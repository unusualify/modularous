---
sidebarTitle: Locale
sidebarPos: 23
---

# Locale

`VInputLocale` is a **translation wrapper** that renders any Vuetify/Modularous input component once per configured language. It handles splitting a single `modelValue` object keyed by locale (`{ en: 'Hello', tr: 'Merhaba' }`) into per-language bindings and merges changes back. There is no corresponding PHP hydrate — the locale wrapping is handled by the hydrate pipeline for translatable fields.

## Vue Component

**Registered as:** `VInputLocale`
**File:** `vue/src/js/components/inputs/Locale.vue`

### Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `modelValue` | `Object` | — | Locale-keyed value object, e.g. `{ en: 'Hello', tr: 'Merhaba' }` |
| `type` | `String` | `'text'` | The underlying component to render per language (e.g. `'v-text-field'`, `'v-select'`, `'v-textarea'`) |
| `attributes` | `Object` | `{}` | Props forwarded to the inner component for each language |
| `initialValues` | `Object` | `{}` | Pre-set values per locale for initialisation |

### Emits

Standard `makeInputEmits` — `update:modelValue` with the updated locale object.

### Usage

```vue
<VInputLocale
  v-model="form.title"
  type="v-text-field"
  :attributes="{
    label: 'Title',
    variant: 'outlined',
    rules: ['required'],
  }"
/>
```

### Behaviour

- If the application has **multiple languages configured**, one instance of the `type` component is rendered per language. Only the currently active locale is visible (others are `d-none`); a locale chip in the field label lets users switch.
- If only **one language** is configured, the wrapper renders a single instance with no locale UI.
- The locale chip appears inside the field label slot when the field is focused or active.
- `attributes` can contain an `items` object keyed by locale (`{ en: [...], tr: [...] }`) — the wrapper will pass the correct locale's items to each instance automatically.
- `attributes.errorMessages` is distributed per locale from the backend validation response.
- If the application is using a `CustomFormBase` root, all locale variants are rendered simultaneously (`isCustomForm = true`) rather than showing only the active one.

### Language object shape

Each language in the application store has:

```js
{ value: 'en', label: 'English', published: true }
```

`published: true` marks the locale as required — fields for unpublished locales are not required even if `rules` includes `'required'`.

## See Also

- [Forms overview](/guide/form-inputs/overview) — Schema-driven form architecture
- [Hydrates reference](/system-reference/hydrates) — How translatable fields are hydrated on the backend
