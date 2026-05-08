---
sidebarTitle: useNavigationLayout
---

# useNavigationLayout

Merges PHP-defined navigation config defaults with persisted user UI preferences to produce the final topbar and bottom-nav options. Used by `Main.vue` and `useSidebar` to determine which navigation elements to show and on which breakpoints.

**File:** `vue/src/js/hooks/useNavigationLayout.js`

---

## Usage

```js
import { useNavigationLayout } from '@/hooks'

const {
  topbarOptions,
  bottomNavOptions,
  showTopbar,
  showBottomNav,
  persistUiPreferences
} = useNavigationLayout()
```

```html
<v-app-bar v-if="showTopbar" />
<v-bottom-navigation v-if="showBottomNav" />
```

## Returns

| Name | Type | Description |
|------|------|-------------|
| `topbarOptions` | `ComputedRef<Object>` | Merged topbar config (defaults + user prefs from `store.state.config.uiPreferences.topbar`) |
| `bottomNavOptions` | `ComputedRef<Object>` | Merged bottom-nav config (defaults + user prefs) |
| `showTopbar` | `ComputedRef<Boolean>` | True when topbar is enabled and should appear on the current breakpoint |
| `showBottomNav` | `ComputedRef<Boolean>` | True when bottom nav is enabled and should appear on the current breakpoint |
| `persistUiPreferences` | `async (preferences) => void` | Commits preferences to Vuex and PUTs them to `store.state.config.uiPreferencesEndpoint` |

## Config shape

Backend sends navigation options via Inertia shared data (`store.state.config.*`):

```php
// In HandleInertiaRequests
'topbarOptions' => [
    'enabled'       => true,
    'fixed'         => false,
    'showOnMobile'  => true,
    'showOnDesktop' => true,
],
'bottomNavigationOptions' => [
    'enabled'       => false,
    'showOnMobile'  => true,
    'showOnDesktop' => false,
],
'uiPreferencesEndpoint' => '/user/ui-preferences',
```

## Persisting preferences

```js
// Save sidebar width and topbar visibility
await persistUiPreferences({
  topbar:  { enabled: false },
  sidebar: { width: 300 }
})
```

`persistUiPreferences` is a fire-and-forget call — it commits to Vuex immediately so the UI reacts, then attempts a PUT in the background. Failures are logged as warnings and do not revert the local state.

## See Also

- [useSidebar](/system-reference/frontend/composables/use-sidebar) — uses `persistUiPreferences` for rail and width persistence
