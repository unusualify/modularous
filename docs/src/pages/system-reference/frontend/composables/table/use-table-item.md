---
sidebarTitle: useTableItem
---

# useTableItem

Holds the currently edited row and provides soft-delete detection helpers.

**File:** `vue/src/js/hooks/table/useTableItem.js`

---

## Usage

```js
import useTableItem from '@/hooks/table/useTableItem'

const {
  editedItem,
  isSoftDeletableItem,
  itemIsDeleted,
  setEditedItem,
  resetEditedItem,
  isSoftDeletable,
  isDeleted,
} = useTableItem(props, context)
```

## Returns

### State

| Name | Type | Description |
|------|------|-------------|
| `editedItem` | `Ref<Object>` | The row currently open in the form. Initialized from `props.modelValue` or a blank model derived from `formSchema`. |
| `isSoftDeletableItem` | `ComputedRef<Boolean>` | `true` when `editedItem.deleted_at` is set (soft-deleted) |
| `itemIsDeleted` | `ComputedRef<Boolean>` | Alias for `isSoftDeletableItem` |

### Methods

| Name | Signature | Description |
|------|-----------|-------------|
| `setEditedItem` | `(item: Object) => void` | Replace `editedItem` with a shallow copy of `item` |
| `resetEditedItem` | `() => void` | Reset `editedItem` to a blank model (runs in `nextTick`) |
| `isSoftDeletable` | `(item: Object) => Boolean` | Returns `true` when `item.deleted_at` is truthy |
| `isDeleted` | `(item: Object) => Boolean` | Alias for `isSoftDeletable` |

## Notes

- `resetEditedItem` uses `nextTick` to avoid clearing the form before the close animation finishes.
- Soft-delete detection drives which delete action is shown (`delete` vs `forceDelete`) and which dialog text is used.

## See Also

- [useTableForms](/system-reference/frontend/composables/table/use-table-forms) — uses `editedItem` for the form model
- [useTableNames](/system-reference/frontend/composables/table/use-table-names) — uses soft-delete state for dialog text
- [useTableItemActions](/system-reference/frontend/composables/table/use-table-item-actions) — triggers `setEditedItem` before actions
