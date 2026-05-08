---
sidebarTitle: useAlert
---

# useAlert

Triggers a global alert notification by committing to the Vuex `alert` store module.

**File:** `vue/src/js/hooks/useAlert.js`

---

## Usage

```js
import { useAlert } from '@/hooks'

const { openAlert } = useAlert()

openAlert({ message: 'Saved successfully', variant: 'success' })
openAlert({ message: 'Something went wrong', variant: 'error' })
```

## Returns

| Name | Type | Description |
|------|------|-------------|
| `openAlert` | `(payload) => void` | Commit an alert to the store |

## Payload

| Key | Type | Description |
|-----|------|-------------|
| `message` | `String` | Alert message text |
| `variant` | `String` | `'success'` \| `'error'` \| `'warning'` \| `'info'` |
| `location` | `String` | Optional. Vuetify snackbar location, e.g. `'top'` |

## Notes

- The alert is rendered by the global snackbar component that reads from `store.state.alert`.
- `useItemActions` and table hooks call `openAlert` internally after server actions complete.

## See Also

- [useItemActions](/system-reference/frontend/composables/use-item-actions) — calls `openAlert` on action success/error
