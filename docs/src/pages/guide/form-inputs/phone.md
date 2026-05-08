---
sidebarTitle: Phone
sidebarPos: 25
---

# Phone

`VInputPhone` is an international phone number input combining a country flag/dial-code selector (`v-autocomplete`) with a text field for the number. It validates the number using `awesome-phonenumber` and auto-detects the country from the number prefix. There is no corresponding PHP hydrate — this component is used directly in Vue templates.

## Vue Component

**Registered as:** `VInputPhone`
**File:** `vue/src/js/components/inputs/Phone.vue`

The `modelValue` is always stored in the **international** format (e.g. `+905551234567`) when the number is valid; national format otherwise.

### Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `modelValue` | `String` | — | Phone number string (v-model) |
| `disabled` | `Boolean` | `false` | Disables both the country selector and the number field |
| `defaultCountry` | `String` | `'TR'` | ISO2 country code pre-selected on mount |
| `preferredCountries` | `Array` | `[]` | ISO2 codes listed at the top of the country dropdown |
| `onlyCountries` | `Array` | `[]` | Restrict the dropdown to these ISO2 codes only |
| `ignoredCountries` | `Array` | `[]` | Exclude these ISO2 codes from the dropdown |
| `allCountries` | `Array` | *(full list)* | Override the full country list |
| `mode` | `String` | `'national'` | Output mode: `'national'` or `'international'` |
| `disabledFetchingCountry` | `Boolean` | `false` | Skip auto-detecting the user's country via IP |
| `invalidMsg` | `String` | `'Invalid phone number'` | Validation error message shown for invalid numbers |

### Emits

| Event | Payload | Description |
|-------|---------|-------------|
| `update:modelValue` | `String` | Formatted phone number |
| `validate` | `Object` | `awesome-phonenumber` result object with `.valid`, `.number`, `.country` |
| `country-changed` | `Object` | Active country object `{ iso2, dialCode, name }` |
| `input` / `blur` / `focus` / `change` | — | Standard field events |

### Usage

```vue
<VInputPhone
  v-model="form.phone"
  defaultCountry="TR"
  :preferredCountries="['TR', 'US', 'DE']"
  @validate="onValidate"
/>
```

```vue
<!-- Restrict to a subset of countries -->
<VInputPhone
  v-model="form.phone"
  :onlyCountries="['TR', 'US', 'GB', 'DE', 'FR']"
/>
```

### Behaviour

- The country is **auto-detected** when the number starts with `+`; the flag and dial code update automatically.
- A built-in `phoneRule` is appended to `rules` and fires `awesome-phonenumber` validation on every input.
- If `disabledFetchingCountry` is `false`, the component attempts an IP-based country lookup on mount to pre-select the user's country.
- `preferredCountries` are listed first in the dropdown, separated from the rest by a divider.
- The text field placeholder updates to a sample number for the active country.

## See Also

- [Forms overview](/guide/form-inputs/overview) — Schema-driven form architecture
