---
sidebarTitle: useRoot
---

# useRoot

Provides access to the Vuetify instance and the Vue root component instance after mount. This is a low-level hook used internally; most features it was originally designed for have been superseded by Vuetify's `useDisplay` and the Vuex store.

**File:** `vue/src/js/hooks/useRoot.js`

---

## Usage

```js
import { useRoot } from '@/hooks'

// Currently returns an empty object — see Notes
useRoot()
```

## Notes

- The hook initialises `vuetifyInstance` and `rootInstance` in `onMounted`, but the reactive state and most methods in it are currently commented out.
- It is imported by `useFile` and `useImage` for historical reasons; the actual Vuetify display utilities used in those hooks come from `useDisplay` directly.
- **Do not rely on this hook** for Vuetify display breakpoints — use Vuetify's `useDisplay()` composable instead.

## See Also

- [useNavigationLayout](/system-reference/frontend/composables/use-navigation-layout) — uses `useDisplay` for responsive layout
- [useSidebar](/system-reference/frontend/composables/use-sidebar) — uses `useDisplay` for rail/expand behaviour
