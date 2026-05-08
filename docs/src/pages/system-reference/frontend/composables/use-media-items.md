---
sidebarTitle: useMediaItems
---

# useMediaItems

Manages the selected item list inside the media library picker — tracking selection state, used items, and shift-click range selection.

**File:** `vue/src/js/hooks/useMediaItems.js`

---

## Usage

```js
import { useMediaItems } from '@/hooks'

const {
  itemsLoading,
  replacingMediaIds,
  isSelected,
  isUsed,
  toggleSelection,
  shiftToggleSelection
} = useMediaItems()
```

```html
<media-item
  v-for="item in items"
  :key="item.id"
  :selected="isSelected(item)"
  :used="isUsed(item)"
  @click="toggleSelection(item)"
  @shift-click="shiftToggleSelection(item, items)"
/>
```

## Returns

### State

| Name | Type | Description |
|------|------|-------------|
| `itemsLoading` | `Ref<Boolean>` | `true` while media items are being fetched |
| `replacingMediaIds` | `Ref<Array<Number>>` | IDs of items currently being replaced (upload in progress) |

### Methods

| Name | Signature | Description |
|------|-----------|-------------|
| `isSelected` | `(item) => Boolean` | Returns `true` when `item` is in the current selection |
| `isUsed` | `(item) => Boolean` | Returns `true` when `item` is already attached to the form field |
| `toggleSelection` | `(item) => void` | Add or remove `item` from the selection |
| `shiftToggleSelection` | `(item, allItems) => void` | Shift-click range selection — selects all items between the last clicked item and `item` in `allItems` |

## Notes

- Selection state is local to the media library modal; it resets when the modal closes.
- `shiftToggleSelection` relies on the order of `allItems` matching the visual order in the grid.

## See Also

- [useMediaLibrary](/system-reference/frontend/composables/use-media-library) — open/close the media library modal
- [useFile](/system-reference/frontend/composables/use-file) — file field input state
- [useImage](/system-reference/frontend/composables/use-image) — image field input state
