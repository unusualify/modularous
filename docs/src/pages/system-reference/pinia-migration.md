---
sidebarPos: 10
sidebarTitle: Pinia Migration
---

# Pinia Migration Path

Modularous currently uses Vuex 4. For new projects, Pinia is the recommended state management library for Vue 3.

## Current State

- Vuex 4 with modules: config, user, alert, language, mediaLibrary, browser, cache, ambient
- Mutations via constants (CONFIG, USER, ALERT, etc.)
- `useStore()` in composables

## Migration Strategy

1. **Short-term**: Keep Vuex. No breaking changes.
2. **Medium-term**: Add Pinia alongside Vuex. Create `store/pinia/` with equivalent modules.
3. **Long-term**: Migrate composables to use Pinia; deprecate Vuex.

## Pinia Module Equivalents

| Vuex Module | Pinia Store |
|-------------|-------------|
| config | useConfigStore() |
| user | useUserStore() |
| alert | useAlertStore() |
| language | useLanguageStore() |
| mediaLibrary | useMediaLibraryStore() |

## Wrapper Pattern

For easier migration, use `storeToRefs`-style access in composables:

```js
// Current (Vuex)
const store = useStore()
store.state.config.isInertia

// Future (Pinia)
const configStore = useConfigStore()
const { isInertia } = storeToRefs(configStore)
```

## Target Version

Pinia migration is planned for Modularous v4.x. No timeline set.
