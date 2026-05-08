---
sidebarTitle: useTableFilters
---

# useTableFilters

Manages the table's search field, status-tab filter, and advanced filter panel — including active counts and reset/clear operations.

**File:** `vue/src/js/hooks/table/useTableFilters.js`

---

## Props Factory

```js
import { makeTableFiltersProps } from '@/hooks/table/useTableFilters'
```

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `hideSearchField` | `Boolean` | `false` | Hide the search input |
| `navActive` | `String` | `'all'` | Initial active status-tab slug |
| `filterBtnOptions` | `Object` | `{}` | Options for the filter button component |
| `searchInitialValue` | `String` | `''` | Initial search string |
| `filterList` | `Array` | `[]` | Status tab definitions `[{ slug, name, number }]` |
| `filterListAdvanced` | `Object` | `{}` | Advanced filter panel definitions by category |
| `hideFilters` | `Boolean` | `false` | Hide the status-tab filter bar |
| `hideAdvancedFilters` | `Boolean` | `false` | Hide the advanced filter button |
| `showMobileHeaders` | `Boolean` | `false` | Show column headers on mobile |

## Usage

```js
import useTableFilters, { makeTableFiltersProps } from '@/hooks/table/useTableFilters'

const {
  search,
  searchModel,
  activeFilterSlug,
  activeFilter,
  mainFilters,
  advancedFilters,
  activeAdvancedFilters,
  activeFilterCount,
  setSearchValue,
  setFilterSlug,
  clearAdvancedFilter,
} = useTableFilters(props)
```

## Returns

### State

| Name | Type | Description |
|------|------|-------------|
| `search` | `Ref<String>` | Committed search value (triggers `loadItems`) |
| `searchModel` | `Ref<String>` | Bound to the search `<v-text-field>` (not yet committed) |
| `activeFilterSlug` | `Ref<String>` | Currently selected status-tab slug |
| `activeFilter` | `ComputedRef<Object>` | The full filter object for the active slug |
| `mainFilters` | `Ref<Array>` | Status tab list (can be updated by `setMainFilters`) |
| `advancedFilters` | `Ref<Object>` | Advanced filter state by category |
| `activeAdvancedFilters` | `ComputedRef<Object>` | Only the categories/filters that have a non-empty selection |
| `activeFilterCount` | `ComputedRef<Number>` | Total number of active advanced filters |
| `expandedPanels` | `Ref<Array>` | Which advanced filter panels are expanded |
| `advancedFilterMenuOpen` | `Ref<Boolean>` | Whether the advanced filter menu is open |
| `filterBtnTitle` | `ComputedRef<Object>` | Text for the filter button (includes active filter count) |

### Methods

| Name | Signature | Description |
|------|-----------|-------------|
| `setSearchValue` | `(value?) => Boolean` | Commit a new search value; returns `true` if changed |
| `setFilterSlug` | `(slug) => Boolean` | Change the active status tab; returns `true` if changed |
| `setMainFilters` | `(filters) => void` | Replace the status tab list |
| `setAdvancedFilters` | `(filters) => void` | Replace the advanced filter state |
| `clearAdvancedFilter` | `() => void` | Clear all advanced filter selections |
| `resetAdvancedFilter` | `() => void` | Reset all filters (alias for clear with different empty value semantics) |
| `resetCategoryFilters` | `(category) => void` | Reset filters for a single category |
| `removeAdvancedFilter` | `(category, slug) => void` | Remove a single filter |
| `getCategoryLabel` | `(category) => String` | Human-readable label for a filter category |
| `getActiveCategoryFilterCount` | `(category) => Number` | Count of active filters in a category |
| `getFilterLabel` | `(category, slug) => String` | Label for a specific filter |
| `formatFilterValue` | `(category, slug) => String` | Display string for a filter's current value |

## Notes

- `search` and `searchModel` are separate: `searchModel` is bound to the input and only commits to `search` when `setSearchValue` is called (e.g. on Enter or blur). This prevents a `loadItems` call on every keystroke.
- Initial state restores from `useTableState.lastParameters` so filter/search survive page refreshes.

## See Also

- [useTableState](/system-reference/frontend/composables/table/use-table-state) — URL/localStorage persistence
- [useTable](/system-reference/frontend/composables/use-table) — orchestrating composable
