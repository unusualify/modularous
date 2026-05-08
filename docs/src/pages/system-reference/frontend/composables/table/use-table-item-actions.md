---
sidebarTitle: useTableItemActions
---

# useTableItemActions

Dispatches per-row actions (edit, delete, restore, duplicate, switch, link, bulk, custom form, show data) and computes which actions are visible for a given row.

**File:** `vue/src/js/hooks/table/useTableItemActions.js`

---

## Props Factory

```js
import { makeTableItemActionsProps } from '@/hooks/table/useTableItemActions'
```

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `isRowEditing` | `Boolean` | — | Whether the row is currently being inline-edited |
| `hideMobileActions` | `Boolean` | `false` | Hide action buttons on mobile |
| `rowActionsIcon` | `String` | `'mdi-cog-outline'` | Icon for the dropdown trigger button |
| `rowActions` | `Array\|Object` | `[]` | Action definitions for each row |
| `rowActionsType` | `String` | `'inline'` | `'inline'` renders icons; `'dropdown'` renders a menu |
| `iteratorType` | `String` | `''` | Set to `'iterator'` when used in card/list view |

## Usage

```js
import useTableItemActions, { makeTableItemActionsProps } from '@/hooks/table/useTableItemActions'

const { itemAction, itemHasAction, visibleRowActions, actionShowingType, actionEvents } =
  useTableItemActions(props, { TableForms, loadItems, TableItem, TableNames })
```

## Returns

| Name | Type | Description |
|------|------|-------------|
| `itemAction` | `(item, action, ...args) => void` | Dispatch an action for a row |
| `itemHasAction` | `(item, action) => Boolean` | Returns `true` when the action should be shown for this item |
| `visibleRowActions` | `ComputedRef<Array>` | Processed action definitions with resolved icons, colors, and component props |
| `actionShowingType` | `ComputedRef<'inline'\|'dropdown'>` | Whether actions render inline or in a dropdown (auto-collapses on small screens) |
| `actionEvents` | `Reactive<Object>` | Shared event bus `{ event, payload, reset() }` consumed by `DataTable` to open dialogs |

## Built-in Action Names

| Name | Behavior |
|------|----------|
| `edit` | Opens the form modal with the row's data |
| `delete` / `forceDelete` | Triggers a confirmation dialog then calls `datatableApi.delete` / `forceDelete` |
| `restore` | Triggers `datatableApi.restore` |
| `duplicate` | Strips `id` from the item and opens the create form |
| `switch` | Sends a `PUT` to `endpoints.update` with the toggled key/value |
| `link` | Opens `item.href` or `action.url` (`:id` replaced) in the configured target |
| `activate` | Sets the active row in the table |
| `bulkDelete` / `bulkForceDelete` / `bulkRestore` / `bulkPublish` | Bulk operations via dialog confirmation |

## Custom Actions

Any action with a `form` property triggers `handleCustomFormAction`, which loads a custom schema/model into the `useTableForms` custom-form modal.

Any action with a `show` property triggers `handleShowAction`, which opens the show-data modal with the relevant item data.

## Permission Checks

`itemHasAction` uses `can(action.name, permissionName)` for built-in actions. It also evaluates `action.conditions` and `action.userConditions` against the row item and the user profile respectively.

## Responsive Collapse

`actionShowingType` automatically switches to `'dropdown'` on smaller viewports when the number of actions exceeds the breakpoint threshold (2+ actions on mobile, 3+ on tablet, etc.).

## See Also

- [useTableForms](/system-reference/frontend/composables/table/use-table-forms) — form modal opened by edit/duplicate
- [useTableItem](/system-reference/frontend/composables/table/use-table-item) — edited item state
- [useAuthorization](/system-reference/frontend/composables/use-authorization) — permission checks
