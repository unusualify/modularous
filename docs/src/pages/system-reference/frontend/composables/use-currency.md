---
sidebarTitle: useCurrency
---

# useCurrency

Provides a `formatPrice` helper that formats a numeric amount as a localized currency string.

**File:** `vue/src/js/hooks/useCurrency.js`

---

## Usage

```js
import { useCurrency } from '@/hooks'

const { formatPrice } = useCurrency()

const display = formatPrice(1999.5, '€')
// → '€ 1.999,50'  (locale-dependent)
```

## Returns

| Name | Signature | Description |
|------|-----------|-------------|
| `formatPrice` | `(amount: Number, symbol?: String) => String` | Format `amount` as a currency string. Uses the active i18n locale to apply thousand-separators and decimal notation. `symbol` is prepended to the result. |

## Notes

- Delegates formatting to the `formatCurrencyPrice` utility from `@/utils/`.
- The locale used is the current vue-i18n active locale, so the decimal separator and grouping separator change automatically with the user's language.
- When `symbol` is omitted the amount is formatted without a currency prefix.

## See Also

- [useCurrencyNumber](/system-reference/frontend/composables/use-currency-number) — wraps `vue-currency-input` for interactive number inputs
- [useLocale](/system-reference/frontend/composables/use-locale) — active locale helpers
