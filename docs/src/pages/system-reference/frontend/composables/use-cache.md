---
sidebarTitle: useCache
---

# useCache

A client-side key-value cache backed by the Vuex `cache` store module. Useful for memoising computed results or storing temporary UI state between component mounts.

**File:** `vue/src/js/hooks/useCache.js`

---

## Usage

```js
import { useCache } from '@/hooks'

const { get, put, push, last, forget, states } = useCache()

// Store a value
put('selectedCurrency', 'USD')

// Retrieve it (with a fallback)
const currency = get('selectedCurrency', 'TRY')

// Append to an array stored at a key
push('recentIds', 42)

// Get the last appended value
const lastId = last('recentIds')

// Delete a key
forget('selectedCurrency')
```

## Returns

| Name | Signature | Description |
|------|-----------|-------------|
| `get` | `(key, defaultValue?) => any` | Returns the cached value or `defaultValue` |
| `put` | `(key, value) => void` | Sets or replaces a cache entry |
| `push` | `(key, value) => void` | Appends `value` to an array stored at `key` |
| `last` | `(key, defaultValue?) => any` | Returns the last element pushed to `key` |
| `forget` | `(key) => void` | Removes a cache entry |
| `states` | `ComputedRef<Object>` | Reactive reference to the entire `store.state.cache` |

## Notes

- The cache is **in-memory only** — it resets on page reload.
- The cache is **global** (stored in Vuex), so any component can read values set by another.
- For persistent storage across navigations, use `localStorage` or server-side preferences.

## See Also

- [useNavigationLayout](/system-reference/frontend/composables/use-navigation-layout) — uses `persistUiPreferences` for durable UI state
