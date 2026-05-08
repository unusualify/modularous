---
sidebarTitle: useFormBaseLogic
---

# useFormBaseLogic

Core logic for the `FormBase` component — flattens nested schema sections, binds schema fields to the model, and manages drag-and-drop reordering, cascade selects, and grid layout attributes.

**File:** `vue/src/js/hooks/useFormBaseLogic.js`

---

## Usage

```js
import { useFormBaseLogic } from '@/hooks'

const {
  flatCombinedArray,
  flatCombinedArraySorted,
  bindSchema,
  onInput,
  mapTypeToComponent,
  getGridAttributes,
} = useFormBaseLogic(props, context)
```

## Returns

### Computed

| Name | Type | Description |
|------|------|-------------|
| `flatCombinedArray` | `ComputedRef<Array>` | Flat list of all schema fields across all sections, in declaration order |
| `flatCombinedArraySorted` | `ComputedRef<Array>` | Same list sorted by `sidebarPos` / `order`, respecting drag-and-drop overrides |

### Methods

| Name | Signature | Description |
|------|-----------|-------------|
| `bindSchema` | `(field, model) => Object` | Merges a schema field definition with the current model value, returning a fully bound field object ready for an input component |
| `onInput` | `(key, value) => void` | Propagates a field value change upward to the parent form and triggers cascade-select resolution |
| `mapTypeToComponent` | `(type: String) => String` | Resolves a schema field `type` string (e.g. `'text'`, `'select'`, `'repeater'`) to its corresponding Vue component name |
| `getGridAttributes` | `(field) => Object` | Returns Vuetify grid props (`cols`, `sm`, `md`, `lg`) for a schema field based on its `grid` config |

### Drag-and-Drop

| Name | Type | Description |
|------|------|-------------|
| `dragstart` | `Function` | `dragstart` event handler — marks the dragged field |
| `dragover` | `Function` | `dragover` event handler — allows drop |
| `drop` | `Function` | `drop` event handler — reorders `flatCombinedArraySorted` |

## Cascade Selects

When a field has `cascade` configured, `onInput` automatically fetches the dependent field's options from the server whenever the parent field value changes.

## Slot Name Generators

`useFormBaseLogic` exports helper functions used by `FormBase` to generate slot names for custom field rendering:

```js
// e.g. for a field with key "user_id":
slotName(field)        // → 'field.user_id'
headerSlotName(field)  // → 'header.user_id'
```

## See Also

- [useFormBase](/system-reference/frontend/composables/use-form-base) — thin alias
- [useForm](/system-reference/frontend/composables/use-form) — top-level form that owns model and submission
- [useInput](/system-reference/frontend/composables/use-input) — per-input state
