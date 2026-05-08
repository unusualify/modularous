---
sidebarTitle: useFormBase
---

# useFormBase

Thin alias that calls `useFormBaseLogic` and re-exports its return value. Import `useFormBase` when consuming the combined field-iteration API without caring about the internal split.

**File:** `vue/src/js/hooks/useFormBase.js`

---

## Usage

```js
import { useFormBase } from '@/hooks'

const { flatCombinedArraySorted, bindSchema, onInput } = useFormBase(props, context)
```

## Returns

All values from [useFormBaseLogic](/system-reference/frontend/composables/use-form-base-logic). See that page for the full reference.

## Notes

- `useFormBase` exists purely as a convenience import alias. All logic resides in `useFormBaseLogic`.
- The `FormBase` Vue component imports this hook to render its slot-driven field layout.

## See Also

- [useFormBaseLogic](/system-reference/frontend/composables/use-form-base-logic) — full implementation
- [useForm](/system-reference/frontend/composables/use-form) — top-level form state
