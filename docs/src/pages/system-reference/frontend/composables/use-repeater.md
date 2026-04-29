---
sidebarTitle: useRepeater
---

# useRepeater

Manages a list of repeatable form blocks. Each block has its own scoped schema and model. Handles add, delete, duplicate, drag reorder, uniqueness constraints, and sync between the internal repeater representation and the flat `modelValue` array.

**File:** `vue/src/js/hooks/useRepeater.js`  
**Props factory:** `makeRepeaterProps`

---

## Usage

```js
import { useRepeater, makeRepeaterProps } from '@/hooks'

const props = defineProps({ ...makeRepeaterProps() })
const {
  repeaterModels,
  repeaterSchemas,
  totalRepeats,
  isAddible,
  isDeletable,
  addRepeaterBlock,
  deleteRepeaterBlock,
  duplicateRepeaterBlock,
  onUpdateRepeaterModel
} = useRepeater(props, context)
```

## Props (via `makeRepeaterProps`)

Extends `makeInputProps` and `makeDraggableProps` plus:

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `modelValue` | `Array` | `[]` | The current list of block data |
| `schema` | `Object` | `{}` | Input schema shared by all blocks |
| `max` | `Number` | `-1` | Maximum blocks (-1 = unlimited) |
| `min` | `Number` | `-1` | Minimum blocks (-1 = none required) |
| `label` | `String` | `''` | Repeater label |
| `singularLabel` | `String` | — | Label for a single block (used in add button) |
| `addButtonText` | `String` | `t('ADD NEW')` | Add button label |
| `hasButtonLabel` | `Boolean` | `false` | Append `singularLabel` to the add button |
| `noAddButton` | `Boolean` | `false` | Hide the add button |
| `noHeaders` | `Boolean` | `false` | Skip removing labels from block schema |
| `isUnique` | `Boolean` | `false` | Enforce unique values in the first field |
| `uniqueValue` | `String` | `'id'` | Key used to determine uniqueness |
| `uniqueField` | `String` | `null` | Schema field name that must be unique |
| `asObject` | `Boolean` | `false` | Store as `{ [uniqueField]: {rest} }` instead of an array |
| `formCol` | `Object` | `{ cols: 12 }` | Grid column for each block |
| `autoIdGenerator` | `Boolean` | `true` | Assign an `id` equal to the block index |
| `idResetter` | `String` | `null` | Field key — resets `id` when this field changes |
| `noWaitSourceLoading` | `Boolean` | `false` | Don't wait for source loading before rendering |

## Returns

| Name | Type | Description |
|------|------|-------------|
| `repeaterModels` | `Ref<Array>` | Internal hydrated model list (namespaced keys) |
| `repeaterSchemas` | `ComputedRef<Array>` | Per-block schema list with namespaced field names |
| `totalRepeats` | `ComputedRef<Number>` | Number of blocks |
| `hasRepeaterModels` | `ComputedRef<Boolean>` | True when at least one block exists |
| `isAddible` | `ComputedRef<Boolean>` | True when a new block can be added |
| `isDeletable` | `ComputedRef<Boolean>` | True when a block can be deleted |
| `addButtonIsActive` | `ComputedRef<Boolean>` | True when the add button should be enabled |
| `headers` | `Array` | Column header labels derived from the schema |
| `selectFieldSlots` | `ComputedRef<Array>` | Slot definitions for schema fields that declare `slots` |
| `hasSchemaInputSourceLoading` | `ComputedRef<Boolean>` | True while any schema input is loading remote data |
| `addRepeaterBlock` | `() => void` | Append a new blank block |
| `deleteRepeaterBlock` | `(index) => void` | Remove block at index |
| `duplicateRepeaterBlock` | `(index) => void` | Clone block at index |
| `onUpdateRepeaterModel` | `(value, index) => void` | Update a block's model when a field changes |
| `onUpdateRepeaterSchema` | `(value, index) => void` | Update raw schema when a field updates it (e.g. cascade) |

## Field namespacing

To isolate blocks from each other, every field name is namespaced:

```
repeater{id}[{blockIndex}][{fieldName}]
// e.g. repeater42[0][title]
```

The hook transparently hydrates (namespace) and parses (strip namespace) models on write and read.

## Uniqueness mode

When `isUnique: true`, each block's designated `uniqueField` must have a distinct value. The hook:
- Tracks `uniqueFilledValues` across all blocks.
- Filters the available items in the unique field's select down to unused values.
- Disables the add button when all available values are taken.
- Supports `asObject: true` to store as `{ [uniqueField]: { ...rest } }` instead of an array.

## See Also

- [input-repeater](/guide/form-inputs/input-repeater)
- [input-json-repeater](/guide/form-inputs/input-json-repeater)
- [useDraggable](/system-reference/frontend/composables/use-draggable) — reorder blocks by dragging
