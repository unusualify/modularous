---
sidebarTitle: useTableState
---

# useTableState

Persists and restores table UI state (page, sort, filter) across page refreshes using `localStorage` and URL query parameters.

**File:** `vue/src/js/hooks/table/useTableState.js`

---

## Usage

```js
import { useTableState } from '@/hooks/table'

const {
  lastParameters,
  queryParameters,
  getLastParameters,
  getQueryParameters,
  setLastParameters,
} = useTableState(props, context)
```

## Returns

### State

| Name | Type | Description |
|------|------|-------------|
| `lastParameters` | `Object` | Merged state from `localStorage` + current URL query params, resolved at mount time |
| `queryParameters` | `Object` | Current URL query parameters only (parsed, JSON values decoded) |

### Methods

| Name | Signature | Description |
|------|-----------|-------------|
| `getQueryParameters` | `() => Object` | Parse and return current URL query parameters |
| `getLastParameters` | `() => Object` | Read `localStorage` and merge with URL query params |
| `setLastParameters` | `(parameters: Object) => void` | Persist the current table state to `localStorage` and clean up URL params |

## localStorage Key

State is stored per-route under:

```
table_filters_{window.location.pathname}
```

## Persisted Keys

Only `page`, `itemsPerPage`, `sortBy`, and `filter` are persisted. `groupBy` is excluded — it is client-side only and not restored on reload.

## Notes

- URL query parameters take precedence over `localStorage` when both are present.
- `setLastParameters` calls `removeQueryKeys` to clean `id`, `page`, `itemsPerPage`, `sortBy`, `groupBy`, `filter`, and `replaceUrl` from the URL after persisting to `localStorage`.
- `useTableFilters` reads `lastParameters.search` and `lastParameters.filter.status` at initialization to restore the search and active filter tab.

## See Also

- [useTableFilters](/system-reference/frontend/composables/table/use-table-filters) — consumes `lastParameters` to restore filter/search state
- [useTable](/system-reference/frontend/composables/use-table) — calls `setLastParameters` after each `loadItems`
