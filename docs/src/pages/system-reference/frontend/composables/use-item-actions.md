---
sidebarTitle: useItemActions
---

# useItemActions

Processes the `actions` array from a form schema into executable, filtered, and attribute-cast action buttons. Handles four action types: `request`, `modal`, `download`, and `blank`.

**File:** `vue/src/js/hooks/useItemActions.js`  
**Props factory:** `makeItemActionsProps`

---

## Usage

```js
import { useItemActions, makeItemActionsProps } from '@/hooks'

const props = defineProps({ ...makeItemActionsProps() })
const { allActions, visibleActions, hasVisibleActions, handleAction } = useItemActions(props, context)
```

```html
<template v-if="hasVisibleActions">
  <v-btn
    v-for="action in visibleActions"
    :key="action.label"
    @click="handleAction(action)"
  >
    {{ action.label }}
  </v-btn>
</template>
```

## Props (via `makeItemActionsProps`)

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `isEditing` | `Boolean` | `false` | Whether the form is in edit mode |
| `actions` | `Array\|Object` | `[]` | Action definitions from the module config |

## Action definition shape

```php
// In module config
'actions' => [
    [
        'type'             => 'request',   // 'request' | 'modal' | 'download' | 'blank'
        'label'            => 'Archive $name',
        'endpoint'         => '/items/:id/archive',
        'method'           => 'post',      // for 'request' type
        'params'           => [],          // request body params
        'editable'         => true,        // show in edit mode
        'creatable'        => false,       // show in create mode
        'hasConfirmation'  => true,        // show confirmation modal first
        'reloadOnSuccess'  => true,        // reload page after success
        'reloadDelay'      => 1000,        // ms delay before reload
        'hideOnCondition'  => false,       // hide (not just disable) when conditions fail
        'conditions'       => [],          // item-level conditions
        'userConditions'   => [],          // user profile conditions
        'responseMessage'  => [
            'success' => 'Archived successfully',
            'error'   => 'Archive failed',
        ],
        'confirmationModalAttributes' => [
            'title' => 'Confirm Archive',
        ],
    ],
]
```

## Action types

| Type | Behaviour |
|------|-----------|
| `request` | Sends an Axios request to `endpoint` using `method`. Shows result via `useAlert`. |
| `modal` | Opens a form modal at `endpoint` (uses `store.commit('SET_MODAL', ...)`). |
| `download` | Opens `endpoint` in a new tab via a temporary `<a>` element. |
| `blank` | Opens `endpoint` in a new tab via `window.open`. |

## Returns

| Name | Type | Description |
|------|------|-------------|
| `allActions` | `ComputedRef<Array>` | All actions after attribute casting and condition evaluation |
| `visibleActions` | `ComputedRef<Array>` | Actions that are not hidden and not disabled |
| `hasActions` | `ComputedRef<Boolean>` | True when `allActions.length > 0` |
| `hasVisibleActions` | `ComputedRef<Boolean>` | True when `visibleActions.length > 0` |
| `handleAction` | `(action) => void` | Execute an action; opens confirmation modal if `hasConfirmation` is true |
| `shouldShowAction` | `(action) => Boolean` | Validate action against `isEditing`, conditions, and user conditions |

## Attribute casting

Before rendering, each action's string values are processed with `useCastAttributes`. This lets you reference the editing item in labels and endpoints:

```php
'label'    => 'View $name',        // item.name
'endpoint' => '/items/:id/review', // :id is replaced with item.id
```

## See Also

- [useCastAttributes](/system-reference/frontend/composables/use-cast-attributes) — `$notation` interpolation
- [useDynamicModal](/system-reference/frontend/composables/use-dynamic-modal) — confirmation dialog
- [useAlert](/system-reference/frontend/composables/use-alert) — success/error notifications
