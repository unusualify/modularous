---
sidebarTitle: useDynamicModal
---

# useDynamicModal

Returns the globally registered `ModalService` instance via Vue's `inject`. Use it to open a confirmation or custom modal from anywhere in the component tree without managing modal state locally.

**File:** `vue/src/js/hooks/useDynamicModal.js`

---

## Prerequisites

The `ModalService` plugin must be installed at the app root:

```js
import { ModalService } from '@/plugins/modalService'
app.use(ModalService)
```

Calling `useDynamicModal` without the plugin installed throws:

```
[ModalService] not installed. Did you forget `app.use(ModalService)`?
```

---

## Usage

```js
import { useDynamicModal } from '@/hooks'

const modal = useDynamicModal()

// Open a confirmation dialog
modal.open(null, {
  modalProps: {
    title: 'Are you sure?',
    confirmCallback: async () => {
      await deleteItem()
      return true // close the modal
    }
  }
})
```

## Returns

The hook returns the `ModalService` instance directly. Refer to the `ModalService` plugin API for all available methods (`open`, `close`, etc.).

## How it is used internally

`useItemActions` calls `useDynamicModal().open(...)` when an action has `hasConfirmation: true`:

```php
// Module config
'actions' => [
    [
        'type'            => 'request',
        'label'           => 'Archive',
        'endpoint'        => '/items/:id/archive',
        'hasConfirmation' => true,
        'confirmationModalAttributes' => [
            'title' => 'Confirm Archive',
        ],
    ]
]
```

## See Also

- [useModal](/system-reference/frontend/composables/use-modal) — local modal open/close state
- [useItemActions](/system-reference/frontend/composables/use-item-actions) — uses `useDynamicModal` for confirmation dialogs
