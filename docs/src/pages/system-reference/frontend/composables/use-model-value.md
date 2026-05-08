---
sidebarTitle: useModelValue
---

# useModelValue

A thin wrapper that creates a two-way computed property for `v-model` binding. Used in components that accept a `modelValue` prop and emit `update:modelValue`.

**File:** `vue/src/js/hooks/useModelValue.js`  
**Props factory:** `makeModelValueProps`

---

## Usage

```js
import { useModelValue, makeModelValueProps } from '@/hooks'

const props = defineProps({ ...makeModelValueProps() })
const { activeItem } = useModelValue(props, context)

// Or with a custom name
const { selectedRow } = useModelValue(props, context, 'selectedRow')
```

```html
<child-component v-model="activeItem" />
```

## Props (via `makeModelValueProps`)

| Prop | Type | Description |
|------|------|-------------|
| `modelValue` | `String \| Number \| Object \| Boolean` | The bound value |

## Returns

A reactive object with a single key whose name is the `name` parameter (default `'activeItem'`):

| Name | Type | Description |
|------|------|-------------|
| `[name]` | `ComputedRef` | Get returns `props.modelValue`; set emits `update:modelValue` |

## Example with custom name

```js
const { selectedUser } = useModelValue(props, context, 'selectedUser')
// selectedUser.value reads props.modelValue
// selectedUser.value = x  →  emits update:modelValue with x
```

## See Also

- [useActiveTableItem](/system-reference/frontend/composables/use-active-table-item) — spreads `makeModelValueProps` for its own row selection
