---
sidebarTitle: useInputFetch
---

# useInputFetch

Handles paginated remote data fetching for select-like inputs (autocomplete, combobox, select-scroll). Builds a URL from `endpoint` + `page` + `search` params, appends pages on scroll, and re-fetches on search change.

**File:** `vue/src/js/hooks/useInputFetch.js`  
**Props factory:** `makeInputFetchProps`

---

## Usage

```js
import { useInputFetch, makeInputFetchProps } from '@/hooks'

const props = defineProps({ ...makeInputFetchProps() })
const {
  elements,
  itemsLoading,
  activePage,
  activeLastPage,
  nextPage,
  getItemsFromApi,
  searchOnInputFetch
} = useInputFetch(props, context)
```

```html
<v-select
  :items="elements"
  :loading="itemsLoading"
  @update:search="searchOnInputFetch"
/>
```

## Props (via `makeInputFetchProps`)

Merges `makeSelectProps` and `makePaginationProps`:

| Prop | Type | Description |
|------|------|-------------|
| `endpoint` | `String` | Base URL to fetch items from |
| `itemValue` | `String` | Key used as the option value |
| `itemTitle` | `String` | Key used as the option label |
| `multiple` | `Boolean` | Whether multiple values can be selected |
| `page` | `Number` | Starting page (default `1`) |
| `perPage` | `Number` | Items per page |
| `searchParam` | `String` | Query param name for search (default `'search'`) |

## Returns

| Name | Type | Description |
|------|------|-------------|
| `elements` | `Ref<Array>` | Accumulated items fetched so far |
| `itemsLoading` | `Ref<Boolean>` | True while a request is in flight |
| `activePage` | `Ref<Number>` | Current page number |
| `activeLastPage` | `Ref<Number>` | Last page from the API response (`-1` means not yet loaded) |
| `nextPage` | `Ref<Number>` | Next page to request |
| `getItemsFromApi` | `() => Promise` | Fetch the next page and append to `elements` |
| `searchOnInputFetch` | `(searchVal) => void` | Reset pagination and re-fetch with a new search term |

## Pagination behaviour

- Pages are fetched sequentially. `getItemsFromApi` is a no-op once `nextPage > activeLastPage`.
- On the first call, `activeLastPage` is `-1` (sentinel for "not loaded yet"), which forces an initial fetch.
- When `search` is non-empty, `elements` is replaced (not appended) so results always reflect the query.
- After fetching, if the current `modelValue` is not yet in `elements`, the hook calls itself recursively to fetch more pages until the selected value is found.

## See Also

- [input-select-scroll](/guide/form-inputs/input-select-scroll)
- [input-autocomplete](/guide/form-inputs/input-autocomplete)
- [input-combobox](/guide/form-inputs/input-combobox)
