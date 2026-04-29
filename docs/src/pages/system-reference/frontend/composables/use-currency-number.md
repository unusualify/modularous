---
sidebarTitle: useCurrencyNumber
---

# useCurrencyNumber

Wraps `vue-currency-input` to provide a reactive, locale-aware number input with live formatting.

**File:** `vue/src/js/hooks/useCurrencyNumber.js`

---

## Usage

```js
import { useCurrencyNumber } from '@/hooks'

const { inputRef, formattedValue, numberValue } = useCurrencyNumber(props)
```

```html
<input :ref="inputRef" />
<span>Formatted: {{ formattedValue }}</span>
<span>Raw number: {{ numberValue }}</span>
```

## Returns

| Name | Type | Description |
|------|------|-------------|
| `inputRef` | `Ref` | Template ref that must be bound to the `<input>` element |
| `formattedValue` | `ComputedRef<String>` | The input value rendered as a formatted string (e.g. `'1.234,56'`) |
| `numberValue` | `ComputedRef<Number\|null>` | The underlying numeric value, or `null` when empty |

## Notes

- Uses EUR locale conventions (`de-DE`) for grouping and decimal separators by default; the locale is configurable via props passed to `vue-currency-input`.
- Emits are handled internally by `vue-currency-input`; bind `v-model` through `numberValue` / a watcher rather than listening to native `input` events.

## See Also

- [useCurrency](/system-reference/frontend/composables/use-currency) — simple display-only price formatter
- [useLocale](/system-reference/frontend/composables/use-locale) — active locale helpers
