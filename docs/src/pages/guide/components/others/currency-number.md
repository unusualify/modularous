---
sidebarPos: 4
sidebarTitle: Currency Number
---

# CurrencyNumber (`ue-currency-number`)

`CurrencyNumber` is a `v-text-field` wrapper that formats a numeric model value as a currency string. Formatting logic is provided by the `useCurrencyNumber` composable.

## Usage

```html
<ue-currency-number
  v-model="price"
  label="Price"
  :error-messages="errors.price"
/>
```

## Props

| Prop | Type | Default | Description |
|---|---|---|---|
| `modelValue` | `Number` | — | The raw numeric value to display and edit. |
| `errorMessages` | `Array` | `[]` | Validation error messages forwarded to the underlying `v-text-field`. |

## Attribute forwarding

Only `label` and `error` are forwarded from `$attrs` to the `v-text-field` via `$bindAttributes($lodash.pick($attrs, ['label', 'error']))`. All other attributes (e.g. `density`, `variant`) are not forwarded — set them through the model or composable configuration.

## Slots

All slots defined on the parent are transparently forwarded to the inner `v-text-field`:

```html
<ue-currency-number v-model="price" label="Amount">
  <template #append-inner>
    <span>USD</span>
  </template>
</ue-currency-number>
```

## Formatting

The display value (`formattedValue`) is a computed ref returned by `useCurrencyNumber`. It converts the numeric `modelValue` to a locale-formatted currency string on read, and parses the raw input back to a number on write. The exact locale and currency symbol are configured inside `useCurrencyNumber`.

## Emits

`ue-currency-number` itself does not define custom emits — the `v-text-field` handles `update:modelValue` internally through the composable binding.
