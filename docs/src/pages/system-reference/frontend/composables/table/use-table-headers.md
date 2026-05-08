---
sidebarTitle: useTableHeaders
---

# useTableHeaders

Manages column visibility with `localStorage` persistence. Columns can be toggled off per-route and the selection survives page refreshes.

**File:** `vue/src/js/hooks/table/useTableHeaders.js`

---

## Props Factory

```js
import { makeTableHeadersProps } from '@/hooks/table/useTableHeaders'
```

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `columns` | `Array` | `[]` | Full column definition array |
| `fixedLastColumn` | `Boolean` | `false` | Pin the last column (actions) to the right |
| `hideHeaders` | `Boolean` | `false` | Hide all column headers |
| `headerOptions` | `Array\|Object` | `{}` | Extra props merged into the header row |
| `cellOptions` | `Array\|Object` | `{}` | Extra props merged into each cell |
| `customRow` | `Object` | `{}` | Custom row template definition (hides headers when set) |

## Usage

```js
import useTableHeaders, { makeTableHeadersProps } from '@/hooks/table/useTableHeaders'

const {
  headers,
  headersModel,
  hasSearchableHeader,
  selectedHeaders,
  hideHeaders,
  formattableHeaders,
  removeHeader,
  applyHeaders,
} = useTableHeaders(props)
```

## Returns

### State

| Name | Type | Description |
|------|------|-------------|
| `headers` | `Ref<Array>` | Currently visible columns (filtered from `columns`) |
| `headersModel` | `Ref<Array>` | Full column list with `visible` boolean added to each entry (for the column picker UI) |
| `hasSearchableHeader` | `Ref<Boolean>` | `true` when at least one column has `searchable: true` |

### Computed

| Name | Type | Description |
|------|------|-------------|
| `selectedHeaders` | `ComputedRef<Array>` | Columns with `visible === true` |
| `hideHeaders` | `ComputedRef<Boolean>` | `true` when `props.hideHeaders` is set or `customRow` is present |
| `formattableHeaders` | `ComputedRef<Array>` | Columns with at least one of: `columnEditable`, `removable`, `searchable`, or `groupable` |

### Methods

| Name | Signature | Description |
|------|-----------|-------------|
| `removeHeader` | `(key: String) => void` | Hide the column with the given key and persist to `localStorage` |
| `applyHeaders` | `() => void` | Apply the current `headersModel` visibility state and persist to `localStorage` |

## localStorage Key

Hidden columns are stored per-route under:

```
table_unvisible_columns_{window.location.pathname}
```

## Column Definition Fields

| Field | Type | Description |
|-------|------|-------------|
| `key` | `String` | Unique column identifier |
| `title` | `String` | Display header text |
| `searchable` | `Boolean` | Allow per-column search |
| `removable` | `Boolean` | Allow hiding via the column picker |
| `columnEditable` | `Boolean` | Allow inline cell editing |
| `groupable` | `Boolean` | Allow grouping by this column |
| `groupOrder` | `'asc'\|'desc'` | Default sort order when grouped |

## See Also

- [useTableGroup](/system-reference/frontend/composables/table/use-table-group) — client-side grouping by column
- [useTable](/system-reference/frontend/composables/use-table) — orchestrating composable
