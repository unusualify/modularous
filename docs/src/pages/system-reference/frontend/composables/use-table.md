---
sidebarTitle: useTable
---

# useTable

Main data-table composable. Orchestrates all table sub-hooks, loads items from the server, and provides the full API consumed by the `DataTable` component.

**File:** `vue/src/js/hooks/useTable.js`

---

## Props Factory

```js
import { makeTableProps } from '@/hooks/useTable'
```

Key props (assembled from all sub-hook `makeXxxProps` factories):

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `endpoints` | `Object` | `{}` | URL map: `index`, `store`, `update`, `destroy`, `forceDelete`, `restore`, `show` |
| `columns` | `Array` | `[]` | Column definitions — see [useTableHeaders](/system-reference/frontend/composables/table/use-table-headers) |
| `formSchema` | `Object` | required | Schema for the create/edit form |
| `rowActions` | `Array\|Object` | `[]` | Per-row action definitions — see [useTableItemActions](/system-reference/frontend/composables/table/use-table-item-actions) |
| `filterList` | `Array` | `[]` | Status filter tabs |
| `filterListAdvanced` | `Object` | `{}` | Advanced filter panel definitions |
| `name` | `String` | — | Module name used for i18n and permissions |
| `moduleName` | `String` | — | Override module name for permissions |
| `createOnModal` | `Boolean` | `true` | Open the create form in a modal |
| `editOnModal` | `Boolean` | `true` | Open the edit form in a modal |
| `itemsPerPage` | `Number` | `20` | Default items per page |
| `actions` | `Array` | `[]` | Table-level bulk/toolbar actions |

## Usage

```js
import { useTable, makeTableProps } from '@/hooks/useTable'

const props = defineProps(makeTableProps())
const emit = defineEmits(['update:modelValue'])

const table = useTable(props, { emit })
```

## Returns

`useTable` spreads the return values of all sub-hooks. Key top-level additions:

| Name | Type | Description |
|------|------|-------------|
| `elements` | `Ref<Array>` | The current page of table rows |
| `totalElements` | `Ref<Number>` | Total record count (for pagination) |
| `tableLoading` | `Ref<Boolean>` | `true` while items are being loaded |
| `loadItems` | `() => Promise` | Fetch items from `endpoints.index` using the current filter/sort/page state |
| `sortElements` | `(key, direction) => void` | Sort the table by a column |
| `dataTableRowProps` | `(item) => Object` | Returns Vuetify row props (click handlers, classes) for each row |
| `headersForDataTable` | `ComputedRef<Array>` | Processed column definitions ready for `v-data-table` |
| `options` | `Ref<Object>` | Vuetify data-table options object (page, itemsPerPage, sortBy, groupBy) |

## Sub-hooks Orchestrated

`useTable` composes the following sub-hooks internally:

| Sub-hook | Responsibility |
|----------|---------------|
| [useTableState](/system-reference/frontend/composables/table/use-table-state) | URL/localStorage state persistence |
| [useTableItem](/system-reference/frontend/composables/table/use-table-item) | Edited item and soft-delete detection |
| [useTableNames](/system-reference/frontend/composables/table/use-table-names) | i18n titles and delete dialog text |
| [useTableFilters](/system-reference/frontend/composables/table/use-table-filters) | Search, status tabs, advanced filters |
| [useTableHeaders](/system-reference/frontend/composables/table/use-table-headers) | Column visibility, localStorage persistence |
| [useTableForms](/system-reference/frontend/composables/table/use-table-forms) | Create/edit form state |
| [useTableItemActions](/system-reference/frontend/composables/table/use-table-item-actions) | Row action dispatch |
| [useTableModals](/system-reference/frontend/composables/table/use-table-modals) | Delete/custom/show modals |
| [useTableActions](/system-reference/frontend/composables/table/use-table-actions) | Table-level toolbar actions |
| [useTableGroup](/system-reference/frontend/composables/table/use-table-group) | Client-side column grouping |

## Data Loading

`loadItems` builds an axios GET request to `endpoints.index` including:
- Current page, items-per-page, and sort-by from `options`
- Active search string
- Active status filter slug
- Active advanced filters

Results are stored in `elements` and `totalElements`.

## See Also

- [useActiveTableItem](/system-reference/frontend/composables/use-active-table-item) — active row / detail-panel state
- [useFormatter](/system-reference/frontend/composables/use-formatter) — column value formatters
