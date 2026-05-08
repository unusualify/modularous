---
sidebarTitle: useTableGroup
---

# useTableGroup

Provides client-side column grouping for Vuetify's `v-data-table`. Columns opt-in via `groupable: true`; only one group key can be active at a time.

**File:** `vue/src/js/hooks/table/useTableGroup.js`

---

## Usage

```js
import useTableGroup from '@/hooks/table/useTableGroup'

const {
  groupKeys,
  hasGroupableColumns,
  selectedGroupKey,
  isGroupingActive,
  toggleGroupByColumn,
  clearGroupBy,
  groupLabelForKey,
} = useTableGroup(props, optionsRef)
```

## Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `props` | `Object` | Component props — reads `props.columns` |
| `optionsRef` | `Ref<Object>` | Vuetify data-table `options` ref — `groupBy` is written here |

## Returns

| Name | Type | Description |
|------|------|-------------|
| `groupKeys` | `ComputedRef<Array<String>>` | Keys of all columns with `groupable: true` |
| `hasGroupableColumns` | `ComputedRef<Boolean>` | `true` when at least one column is groupable |
| `hasGroupMenu` | `ComputedRef<Boolean>` | Alias for `hasGroupableColumns` (deprecated) |
| `selectedGroupKey` | `WritableComputedRef<String\|null>` | The currently active group key, or `null`. Set to activate a group. |
| `isGroupingActive` | `ComputedRef<Boolean>` | `true` when any group is applied |
| `isGroupActiveForKey` | `(key) => Boolean` | Returns `true` when `key` is the active group |
| `toggleGroupByColumn` | `(key) => void` | Activate `key` as the group column, or clear it if already active |
| `clearGroupBy` | `() => void` | Remove all grouping |
| `groupLabelForKey` | `(key) => String` | Returns the column `title` for the given key |

## Column Configuration

Enable grouping on a column by adding `groupable: true` and optionally `groupOrder`:

```js
{
  key: 'status',
  title: 'Status',
  groupable: true,
  groupOrder: 'asc'  // 'asc' | 'desc', default 'asc'
}
```

## Exported Utilities

| Function | Description |
|----------|-------------|
| `normalizeGroupByConfig(list)` | Normalizes a raw `groupBy` list to `[{ key, order }]` |
| `parseGroupByConfigItem(raw)` | Parses a single group-by entry (string or object) |
| `normalizeGroupOrder(order)` | Returns `'asc'` or `'desc'`, defaulting to `'asc'` |
| `pickFetchRelevantOptions(options)` | Strips `groupBy` (client-only) before building an API request |
| `onlyGroupByChanged(old, new)` | Returns `true` when only `groupBy` changed — used to skip redundant API calls |

## Notes

- `groupBy` is client-side only and is never sent to the index API endpoint.
- `onlyGroupByChanged` is used by `useTable`'s options watcher to avoid redundant `loadItems` calls when toggling grouping.

## See Also

- [useTableHeaders](/system-reference/frontend/composables/table/use-table-headers) — column definitions
- [useTable](/system-reference/frontend/composables/use-table) — orchestrating composable
