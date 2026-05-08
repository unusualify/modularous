---
sidebarTitle: useTableActions
---

# useTableActions

Defines props for table-level toolbar actions (bulk operations, custom toolbar buttons).

**File:** `vue/src/js/hooks/table/useTableActions.js`

---

## Props Factory

```js
import { makeTableActionsProps } from '@/hooks/table/useTableActions'
```

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `actionsPosition` | `String` | `'top'` | Where to render toolbar actions: `'top'` or `'bottom'` |
| `actions` | `Array` | `[]` | Action button definitions for the table toolbar |

## Usage

```js
import useTableActions, { makeTableActionsProps } from '@/hooks/table/useTableActions'

const props = defineProps(makeTableActionsProps())
useTableActions(props, context)
```

## Notes

- This hook currently defines props and reserves the extension point for future toolbar-action logic.
- Per-row actions are handled by [useTableItemActions](/system-reference/frontend/composables/table/use-table-item-actions).

## See Also

- [useTableItemActions](/system-reference/frontend/composables/table/use-table-item-actions) — per-row action handlers
- [useTable](/system-reference/frontend/composables/use-table) — orchestrating composable
