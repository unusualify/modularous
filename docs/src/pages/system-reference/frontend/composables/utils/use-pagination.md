---
sidebarTitle: usePagination
---

# usePagination

Manages infinite-scroll / load-more pagination state — page tracking, element accumulation, search, and URL construction.

**File:** `vue/src/js/hooks/utils/usePagination.js`

---

## Props Factory

```js
import { makePaginationProps } from '@/hooks/utils/usePagination'
```

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `endpoint` | `String` | — | Base URL for the paginated data source |
| `page` | `Number` | `1` | Initial page number |
| `lastPage` | `Number` | `-1` | Last known page number (`-1` = unknown) |
| `itemsPerPage` | `Number` | `20` | Items per page |
| `sourceLoading` | `Boolean` | `false` | External loading flag |
| `with` | `Array` | `[]` | Relationships to eager-load |
| `scopes` | `Array` | `[]` | Query scopes to apply |
| `orders` | `Array` | `[]` | Sort order definitions |
| `appends` | `Array` | `[]` | Appended attributes |
| `column` | `Array` | `[]` | Columns to select |
| `searchKeys` | `Array` | `['name']` | Fields to search within |
| `paginationPageKey` | `String` | `'page'` | Query parameter name for the page number |

## Usage

```js
import { usePagination, makePaginationProps } from '@/hooks/utils/usePagination'

const props = defineProps(makePaginationProps())

const {
  elements,
  activePage,
  nextPage,
  activeLastPage,
  itemsLoading,
  fullUrl,
  searchModel,
  appendElements,
  setActivePage,
  setActiveLastPage,
  setItemsLoading,
} = usePagination(props, context)
```

## Returns

### State

| Name | Type | Description |
|------|------|-------------|
| `elements` | `Ref<Array>` | Accumulated list of loaded items |
| `activePage` | `Ref<Number>` | Current page number |
| `nextPage` | `Ref<Number>` | Page to load next |
| `activeLastPage` | `Ref<Number>` | Last known page number |
| `itemsLoading` | `Ref<Boolean>` | `true` while a fetch is in progress |
| `searchModel` | `Ref<String>` | Search input value |
| `search` | `Ref<String>` | Committed search value |

### Computed

| Name | Type | Description |
|------|------|-------------|
| `fullUrl` | `ComputedRef<String>` | Complete URL with all query parameters |
| `queryParameters` | `ComputedRef<String>` | URLSearchParams string (page, itemsPerPage, search, with, …) |
| `searchFilterObject` | `ComputedRef<Object>` | `{ search: value }` when search is non-empty |
| `searchFieldsFilter` | `ComputedRef<Object>` | Multi-key search filter object |
| `defaultQueryParameters` | `Ref<Object>` | Parameters parsed from the original `endpoint` URL |

### Methods

| Name | Signature | Description |
|------|-----------|-------------|
| `setActivePage` | `(page) => void` | Update `activePage` and advance `nextPage` |
| `setActiveLastPage` | `(page) => void` | Update `activeLastPage` |
| `setItemsLoading` | `(value) => void` | Set the loading flag |
| `setElements` | `(items) => void` | Replace the elements list |
| `appendElements` | `(items) => void` | Append items to the end of `elements` |
| `prependElements` | `(items) => void` | Prepend items to the beginning of `elements` |
| `setSearchValue` | `(value?) => void` | Commit a search value |

## Notes

- For infinite-scroll UIs, call `appendElements` on each successful page load and `setActivePage` to advance.
- For standard paginated tables, use `setElements` to replace the list on each load.
- The main data-table uses [useTable](/system-reference/frontend/composables/use-table) directly. `usePagination` is intended for simpler paginated list components.

## See Also

- [useTable](/system-reference/frontend/composables/use-table) — full-featured table composable
