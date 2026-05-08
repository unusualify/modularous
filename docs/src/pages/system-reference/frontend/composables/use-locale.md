---
sidebarTitle: useLocale
---

# useLocale

Provides reactive locale state, RTL detection, and a method to switch the active language.

**File:** `vue/src/js/hooks/useLocale.js`

---

## Usage

```js
import { useLocale } from '@/hooks'

const {
  currentLocale,
  languages,
  hasLocale,
  isLocaleRTL,
  dirLocale,
  displayedLocale,
  updateLocale
} = useLocale()
```

```html
<div :dir="dirLocale">
  <v-select v-model="currentLocale" :items="languages" />
</div>
```

## Returns

### State

| Name | Type | Description |
|------|------|-------------|
| `currentLocale` | `ComputedRef<String>` | Active locale code (e.g. `'en'`, `'ar'`) from `store.state.user.locale` |
| `languages` | `ComputedRef<Array>` | List of available language objects from `store.state.config.languages` |
| `hasLocale` | `ComputedRef<Boolean>` | `true` when `languages` has more than one entry |
| `isLocaleRTL` | `ComputedRef<Boolean>` | `true` when the current locale is in the RTL locales list (Arabic, Hebrew, Persian, Urdu, etc.) |
| `dirLocale` | `ComputedRef<'rtl'\|'ltr'>` | `'rtl'` or `'ltr'` string, ready to bind to the `dir` attribute |
| `displayedLocale` | `ComputedRef<String>` | Display-friendly locale label (title-cased locale code or label from the languages list) |

### Methods

| Name | Signature | Description |
|------|-----------|-------------|
| `updateLocale` | `(locale: String) => void` | Update the Vuex store and vue-i18n locale to the given code |

## Notes

- RTL locales include: `ar`, `he`, `fa`, `ur`, `ps`, `ckb`, `dv`, `ug`, `yi`.
- `updateLocale` mutates both `store.state.user.locale` and the `i18n.global.locale` so all reactive locale-dependent values update simultaneously.

## See Also

- [useUser](/system-reference/frontend/composables/use-user) — authenticated user state (locale is stored on the user)
