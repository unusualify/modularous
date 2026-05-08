---
sidebarTitle: useTableModals
---

# useTableModals

Manages the three modal types used by a data table: the confirmation dialog, a custom content modal, and the show-data (detail view) modal.

**File:** `vue/src/js/hooks/table/useTableModals.js`

---

## Props Factory

```js
import { makeTableModalsProps } from '@/hooks/table/useTableModals'
```

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `openCustomModal` | `Boolean` | `false` | Initialize with the custom modal open |

## Usage

```js
import useTableModals, { makeTableModalsProps } from '@/hooks/table/useTableModals'

const {
  modals,
  customModalActive,
  actionDialogQuestion,
  openCustomModal,
  closeCustomModal,
  setModalType,
} = useTableModals(props, context)
```

## Returns

### State

| Name | Type | Description |
|------|------|-------------|
| `modals` | `Ref<Object>` | Map of modal objects keyed by type: `'dialog'`, `'custom'`, `'show'` |
| `deleteModalActive` | `Ref<Boolean>` | Legacy flag for the delete confirmation modal |
| `customModalActive` | `Ref<Boolean>` | Whether the custom content modal is visible |
| `actionModalActive` | `Ref<Boolean>` | Whether the action-result modal is visible |
| `selectedAction` | `Ref<Object\|null>` | The action that triggered the current modal |
| `activeModal` | `ComputedRef<Object>` | The modal object for the current `activeModalType` |

### Computed

| Name | Type | Description |
|------|------|-------------|
| `actionDialogQuestion` | `ComputedRef<String>` | Translated confirmation question for the active action |

### Methods

| Name | Signature | Description |
|------|-----------|-------------|
| `openCustomModal` | `() => void` | Open the custom modal |
| `closeCustomModal` | `() => void` | Close the custom modal |
| `setModalType` | `(type: String) => void` | Switch the active modal type |
| `bulkAction` | `(action) => void` | Execute a bulk action via `datatableApi` |

## Modal Object API

Each entry in `modals` exposes a consistent interface:

```js
modals.value.dialog.open()           // open the dialog
modals.value.dialog.close()          // close the dialog
modals.value.dialog.toggle(state?)   // toggle or set state

modals.value.dialog.set({ title: 'Delete?', description: '...' })  // set attributes
modals.value.dialog.setConfirmCallback(fn)   // set callback for Confirm button
modals.value.dialog.setRejectCallback(fn)    // set callback for Cancel button
modals.value.dialog.reset()                  // restore previous attribute values
```

The `show` modal additionally has:

```js
modals.value.show.loadData(data)   // populate the detail view
modals.value.show.resetData()      // clear detail view data
```

## Notes

- The `dialog` modal is used for delete confirmations and action confirmations. Its `confirmCallback` / `rejectCallback` are set before opening.
- The `show` modal displays arbitrary item data in a read-only panel.
- The `custom` modal renders a slot-driven content area driven by `store.state.datatable.customModal`.

## See Also

- [useTableItemActions](/system-reference/frontend/composables/table/use-table-item-actions) — triggers modal opens via `actionEvents`
- [useTableNames](/system-reference/frontend/composables/table/use-table-names) — provides `deleteQuestion` / `actionDialogQuestion` text
