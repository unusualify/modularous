---
sidebarTitle: useInertiaRequests
---

# useInertiaRequests

Exposes reactive state for in-flight Inertia.js requests. Use it to display loading indicators or disable UI elements while a navigation or form submission is pending.

**File:** `vue/src/js/hooks/useInertiaRequests.js`

---

## Usage

```js
import { useInertiaRequests, useInertiaLoading } from '@/hooks'

// Full hook
const { isLoading, activeRequestCount, hasActiveRequests } = useInertiaRequests()

// Loading-only shorthand
const { isLoading, loadingText } = useInertiaLoading()
```

```html
<v-progress-linear :active="isLoading" indeterminate />
<v-btn :disabled="isLoading">Save</v-btn>
```

## `useInertiaRequests` Returns

| Name | Type | Description |
|------|------|-------------|
| `activeRequestCount` | `ComputedRef<Number>` | Number of in-flight Inertia requests (from `store.state.config.axiosRequestCount`) |
| `hasActiveRequests` | `ComputedRef<Boolean>` | True when count > 0 |
| `isLoading` | `ComputedRef<Boolean>` | Alias for `hasActiveRequests` |
| `getRequestCount` | `() => Number` | Non-reactive snapshot of the current count |
| `hasRequests` | `() => Boolean` | Non-reactive snapshot of whether requests are active |

## `useInertiaLoading` Returns

| Name | Type | Description |
|------|------|-------------|
| `isLoading` | `ComputedRef<Boolean>` | True while any request is in flight |
| `loadingText` | `ComputedRef<String>` | `''` when idle, `'Loading...'` for one request, `'Loading... (N requests)'` for multiple |
| `activeRequestCount` | `ComputedRef<Number>` | Number of in-flight requests |

## Notes

- The request count is incremented/decremented by Inertia interceptors registered in `vue/src/js/setup/inertia-interceptors.js`.
- For Axios-based requests (non-Inertia), the count comes from `store.state.config.axiosRequestCount`.
