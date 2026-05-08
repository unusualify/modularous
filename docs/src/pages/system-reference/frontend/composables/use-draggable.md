---
sidebarTitle: useDraggable
---

# useDraggable

Provides drag-and-drop props and reactive `dragOptions` for components that use Sortable.js (via `vue-draggable-next`).

**File:** `vue/src/js/hooks/useDraggable.js`  
**Props factory:** `makeDraggableProps`

---

## Usage

```js
import { useDraggable, makeDraggableProps } from '@/hooks'

// In your component
const props = defineProps({ ...makeDraggableProps() })
const { dragOptions } = useDraggable(props, context)
```

```html
<draggable v-bind="dragOptions" v-model="items">
  <template #item="{ element }">...</template>
</draggable>
```

## Props (via `makeDraggableProps`)

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `draggable` | `Boolean` | `false` | Enables drag-and-drop when `true` |
| `orderKey` | `String` | `'position'` | The model key that stores the sort order |

## Returns

| Name | Type | Description |
|------|------|-------------|
| `dragOptions` | `ComputedRef<Object>` | Options object to pass to `<draggable>`. Contains `disabled` (flipped from `props.draggable`) and `animation: 150`. |

## Notes

- When `draggable` is `false`, `dragOptions.disabled` is `true` and the list is static.
- `useRepeater` and `useFile`/`useImage` spread `makeDraggableProps` into their own prop definitions, inheriting drag support automatically.

## See Also

- [useRepeater](/system-reference/frontend/composables/use-repeater) — uses `draggable` + `orderKey` to reorder blocks
- [useFile](/system-reference/frontend/composables/use-file) — file list supports draggable reorder
