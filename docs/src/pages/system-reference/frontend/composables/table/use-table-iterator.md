---
sidebarTitle: useTableIterator
---

# useTableIterator

Composable for card/list (iterator) layout components. Wires up item actions, formatting, and the header key map for a single row item in a non-table view.

**File:** `vue/src/js/hooks/table/useTableIterator.js`

---

## Props Factory

```js
import { makeTableIteratorProps } from '@/hooks/table/useTableIterator'
```

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `name` | `String` | `''` | Module name used for i18n and permissions |
| `titlePrefix` | `String` | `''` | Prefix added before the item title |
| `titleKey` | `String` | `'name'` | The item property used as the display title |
| `item` | `Object` | `{}` | The row data object |
| `headers` | `Object` | `{}` | Column definitions keyed by column key |
| `iteratorOptions` | `Object` | `{}` | Additional iterator display options |
| `rowActions` | `Array` | `[]` | Per-item action definitions |

## Emits

| Event | Payload | Description |
|-------|---------|-------------|
| `click-action` | `(item, action)` | Forwarded from `itemAction` — parent handles the action |
| `edit-item` | `(item)` | Forwarded from `editItem` — parent handles the edit |

## Usage

```js
import useTableIterator, { makeTableIteratorProps } from '@/hooks/table/useTableIterator'

const props = defineProps(makeTableIteratorProps())
const emit = defineEmits(['click-action', 'edit-item'])

const { headersWithKeys, itemHasAction, formatValue, id } = useTableIterator(props, { emit })
```

## Returns

| Name | Type | Description |
|------|------|-------------|
| `id` | `String` | Unique instance ID for the iterator |
| `headersWithKeys` | `ComputedRef<Object>` | Headers object re-keyed by `header.key` for fast lookup |
| `itemHasAction` | `(action) => Boolean` | Whether an action should be shown for the current `props.item` |
| `formatValue` | `(value, header) => any` | Format a cell value using the column's formatter |

## Notes

- `itemAction` and `editItem` emit events upward rather than handling them directly — the parent `DataTable` or iterator container owns the action logic.
- `useTableIterator` is used by card-view and list-view layout components that render each row outside of `v-data-table`.

## See Also

- [useTableItemActions](/system-reference/frontend/composables/table/use-table-item-actions) — action dispatch
- [useFormatter](/system-reference/frontend/composables/use-formatter) — value formatting
- [useTable](/system-reference/frontend/composables/use-table) — orchestrating composable
