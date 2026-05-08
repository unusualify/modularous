---
sidebarTitle: useValidation
---

# useValidation

Provides 30+ validation rule factories, a rule-string-to-function converter, and helpers to generate a complete rule array for a form input.

**File:** `vue/src/js/hooks/useValidation.js`

---

## Usage

```js
import { useValidation } from '@/hooks'

const { generateInputRules, invokeRuleGenerator } = useValidation()

// Generate rules from a schema field definition
const rules = generateInputRules(schemaField)

// Convert a rule string into a callable rule function
const rule = invokeRuleGenerator('required')
```

```html
<v-text-field :rules="generateInputRules(field)" />
```

## Returns

### Core Helpers

| Name | Signature | Description |
|------|-----------|-------------|
| `generateInputRules` | `(field) => Array<Function>` | Reads `field.rules` (array of strings or objects) and returns an array of Vuetify-compatible rule functions |
| `invokeRuleGenerator` | `(rule: String\|Object) => Function` | Resolves a rule name string (e.g. `'required'`) to the corresponding rule factory and returns the callable rule function |
| `validateInput` | `(value, rules) => Boolean\|String` | Runs a value through an array of rules; returns `true` on success or the first error message string |

### Rule Factories

All rule factories return `(value) => true | errorMessage`.

| Rule | Description |
|------|-------------|
| `required` | Value must not be empty / null |
| `email` | Must be a valid e-mail address |
| `phone` | Must be a valid phone number |
| `url` | Must be a valid URL |
| `date` | Must be a parseable date string |
| `min(n)` | String/array length or numeric value ≥ n |
| `max(n)` | String/array length or numeric value ≤ n |
| `minLength(n)` | String length ≥ n |
| `maxLength(n)` | String length ≤ n |
| `minValue(n)` | Numeric value ≥ n |
| `maxValue(n)` | Numeric value ≤ n |
| `numeric` | Must be a number |
| `integer` | Must be an integer |
| `alpha` | Letters only |
| `alphaNum` | Letters and digits only |
| `password` | Must meet password complexity requirements |
| `confirmed(field)` | Must equal the value of another field |
| `unique` | Must be unique (resolved via async endpoint) |
| `regex(pattern)` | Must match the given regex |
| `sameAs(other)` | Must equal `other` |
| `notSameAs(other)` | Must not equal `other` |
| `between(min, max)` | Value must be between min and max |
| `decimal` | Must be a decimal number |
| `ipAddress` | Must be a valid IPv4 or IPv6 address |
| `macAddress` | Must be a valid MAC address |
| `json` | Must be valid JSON |
| `accepted` | Must be truthy (checkbox accepted) |
| `requiredIf(condition)` | Required when `condition` is true |
| `requiredUnless(condition)` | Required unless `condition` is true |
| `maxFileSize(kb)` | File size must not exceed `kb` kilobytes |
| `mimes(types)` | File MIME type must be in `types` list |

## Schema Rule Format

Rules in a schema field are expressed as strings or objects:

```js
{
  rules: [
    'required',
    { name: 'minLength', params: [3] },
    { name: 'maxLength', params: [100] }
  ]
}
```

## See Also

- [useInput](/system-reference/frontend/composables/use-input) — base input state that consumes `generateInputRules`
- [useForm](/system-reference/frontend/composables/use-form) — top-level form validation orchestration
