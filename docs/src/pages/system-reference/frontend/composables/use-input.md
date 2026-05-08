---
sidebarTitle: useInput
---

# useInput

Base composable for all form inputs. Provides `modelValue` binding, schema-driven prop resolution, and the `updateModelValue` / `emitModelValue` contract that every input component must follow.

**File:** `vue/src/js/hooks/useInput.js`

---

## Props Factory

```js
import { makeInputProps } from '@/hooks/useInput'
```

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `modelValue` | `any` | `null` | The bound value (v-model) |
| `schema` | `Object` | `{}` | Schema field definition — label, rules, placeholder, etc. |
| `disabled` | `Boolean` | `false` | Disable the input |
| `readonly` | `Boolean` | `false` | Make the input read-only |
| `clearable` | `Boolean` | `false` | Show a clear button |
| `label` | `String` | `''` | Override the label from the schema |
| `placeholder` | `String` | `''` | Override the placeholder from the schema |
| `hint` | `String` | `''` | Hint text shown below the input |
| `density` | `String` | `'comfortable'` | Vuetify density (`compact`, `comfortable`, `default`) |

## Emits Factory

```js
import { makeInputEmits } from '@/hooks/useInput'
```

| Event | Payload | Description |
|-------|---------|-------------|
| `update:modelValue` | `any` | Emitted when the value changes |
| `input` | `any` | Emitted on every keystroke / change |

## Injects Factory

```js
import { makeInputInjects } from '@/hooks/useInput'
```

Provides access to form-level inject keys (parent form context, schema bindings).

## Usage

```js
import { useInput, makeInputProps, makeInputEmits } from '@/hooks/useInput'

const props = defineProps(makeInputProps())
const emit = defineEmits(makeInputEmits())

const { id, boundProps, input, initialValue, updateModelValue, emitModelValue } = useInput(props, emit)
```

## Returns

| Name | Type | Description |
|------|------|-------------|
| `id` | `ComputedRef<String>` | Unique input ID derived from the schema field name |
| `boundProps` | `ComputedRef<Object>` | Merged props object ready to spread onto the underlying Vuetify input component |
| `input` | `ComputedRef<Object>` | Resolved schema field with all defaults applied |
| `initialValue` | `any` | The value at mount time, used to detect changes |
| `updateModelValue` | `(value) => void` | Set the internal value and emit `update:modelValue` |
| `emitModelValue` | `(value) => void` | Emit `update:modelValue` without updating internal state (use for pass-through) |

## Notes

- `boundProps` automatically merges schema-defined props (label, placeholder, rules, disabled) with explicitly passed props. Explicit props take precedence.
- All hydrate-backed input components (`InputText`, `InputSelect`, etc.) call `useInput` internally.

## See Also

- [useValidation](/system-reference/frontend/composables/use-validation) — rule factories used by `useInput`
- [useModelValue](/system-reference/frontend/composables/use-model-value) — lower-level v-model proxy
- [useForm](/system-reference/frontend/composables/use-form) — top-level form that owns the model
