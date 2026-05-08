---
sidebarTitle: useConfig
---

# useConfig

Provides reactive access to the application's runtime configuration stored in Vuex — environment, app name, Inertia mode, and in-flight request tracking.

**File:** `vue/src/js/hooks/useConfig.js`

---

## Usage

```js
import { useConfig } from '@/hooks'

const {
  isHot,
  appName,
  appEnv,
  shouldUseInertia,
  isRequestInProgress,
  setRequestInProgress,
  increaseAxiosRequest,
  decreaseAxiosRequest
} = useConfig()
```

```html
<v-progress-linear v-if="isRequestInProgress" indeterminate />
<span>{{ appName }} — {{ appEnv }}</span>
```

## Returns

### State

| Name | Type | Description |
|------|------|-------------|
| `isHot` | `ComputedRef<Boolean>` | `true` when the app is running in hot-reload (dev) mode |
| `appName` | `ComputedRef<String>` | Application name from `store.state.config.app_name` |
| `appEnv` | `ComputedRef<String>` | Environment string (`'local'`, `'production'`, etc.) from `store.state.config.app_env` |
| `shouldUseInertia` | `ComputedRef<Boolean>` | `true` when Inertia.js SPA navigation is enabled |
| `isRequestInProgress` | `ComputedRef<Boolean>` | `true` when at least one axios request is currently in-flight |

### Methods

| Name | Signature | Description |
|------|-----------|-------------|
| `setRequestInProgress` | `(value: Boolean) => void` | Directly set the in-progress flag |
| `increaseAxiosRequest` | `() => void` | Increment the in-flight request counter |
| `decreaseAxiosRequest` | `() => void` | Decrement the in-flight request counter |

## Notes

- `isRequestInProgress` is derived from a counter, not a boolean flag — `increaseAxiosRequest` / `decreaseAxiosRequest` allow multiple concurrent requests to be tracked correctly.
- `shouldUseInertia` gates navigation calls: when `true`, use `router.visit()` instead of `window.open()`.

## See Also

- [useInertiaRequests](/system-reference/frontend/composables/use-inertia-requests) — request state hooks
