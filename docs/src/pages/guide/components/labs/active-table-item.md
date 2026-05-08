---
sidebarPos: 10
sidebarTitle: Active Table Item
---

# ActiveTableItem <Badge type="warning" text="experimental" />

`ActiveTableItem` provides a two-phase drill-down UI for a selected table row:

1. **Selection modal** — a `ue-modal` dialog showing a grid of labelled blocks. The user clicks a block to select a sub-section.
2. **Detail panel** — once a block is selected, the modal closes and an inline `ue-table` renders the item's details alongside any additional elements configured for that block.

The component delegates its logic entirely to `useActiveTableItem` and `makeActiveTableItemProps` from the internal `__hooks` module.

> [!NOTE]
> This component is designed for use inside table row-click handlers. It is not a general-purpose component.

## Behaviour

```
User clicks a row
  → ActiveTableItem mounts with item data
  → Modal opens showing block options
  → User clicks a block (selectNested)
    → modalActive = false, activeBlock = selected block
    → ue-table renders item details for that block
    → ue-recursive-stuff renders block.elements below the table
  → User clicks ✕ to close (closeItemDetails)
    → activeBlock = null
```

## Props

Props are defined by `makeActiveTableItemProps()` from the `__hooks` module. The exact shape depends on the hook implementation, but typically includes:

| Prop | Description |
|---|---|
| `item` | The full row data object for the selected record |
| `itemData` | Array of block definitions. Each block controls what appears in the modal and what is rendered in the detail panel. |
| `tableHeaders` | Column definitions forwarded to the detail `ue-table` |

## Block definition

Each entry in `itemData` configures one selectable block in the modal:

| Key | Type | Description |
|---|---|---|
| `title` | `String` | Label shown on the block button |
| `elements` | `Array` | `ue-recursive-stuff` configuration rendered below the detail table |
| `clickBlock` | `Object` | Optional. If present, overrides the block button with a custom layout. `clickBlock.col` sets the `v-col` bindings; `clickBlock.elements` is passed to `ue-recursive-stuff` instead of a plain button. |

## Detail table

The inline `ue-table` is rendered with:
- `is-row-editing: false`
- `create-on-modal: false` / `edit-on-modal: false`
- `row-actions: []`
- `items-per-page: 1` (single-item view)
- Footer, form, and full-screen chrome are hidden
- A close (`✕`) button in `#headerRight` calls `closeItemDetails()`

## Notes

- `ignore-formatters: ['activate']` is passed to prevent the activate formatter from rendering on the detail table.
- The detail panel only shows when `item` is set, `modalActive` is `false`, and `activeBlock` is non-null.
- Customise the block layout using `clickBlock.elements` and `ue-recursive-stuff` configuration objects.
