---
sidebarTitle: useForm
---

# useForm

Top-level form composable. Manages form model state, submission, schema watching, server-side error binding, and orchestrates input `handleInput` / `handleClick` callbacks.

**File:** `vue/src/js/hooks/useForm.js`

---

## Props Factory

```js
import { makeFormProps } from '@/hooks/useForm'
```

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `modelValue` | `Object` | `{}` | The form's data model (v-model) |
| `schema` | `Object` | `{}` | Full schema definition — drives field list, rules, and layout |
| `endpoint` | `String` | `''` | API endpoint for form submission |
| `method` | `String` | `'post'` | HTTP method (`post`, `put`, `patch`) |
| `id` | `String` | auto | HTML form `id` attribute |
| `resetOnSuccess` | `Boolean` | `false` | Reset the model to defaults after a successful submission |
| `redirectOnSuccess` | `String` | `null` | URL to redirect to after success |
| `formActions` | `Array\|Object` | `[]` | Additional action button definitions |
| `hideDefaultActions` | `Boolean` | `false` | Hide the default submit/cancel buttons |
| `submitText` | `String` | `t('Save')` | Label for the primary submit button |
| `cancelText` | `String` | `t('Cancel')` | Label for the cancel button |
| `disabled` | `Boolean` | `false` | Disable all inputs in the form |
| `readonly` | `Boolean` | `false` | Make all inputs read-only |

## Usage

```js
import { useForm, makeFormProps } from '@/hooks/useForm'

const props = defineProps(makeFormProps())
const emit = defineEmits(['update:modelValue', 'success', 'error'])

const {
  model,
  saveForm,
  submit,
  handleInput,
  handleClick,
  setSchemaErrors,
  resetSchemaErrors,
  createSchema,
  validModel,
  formLoading
} = useForm(props, emit)
```

## Returns

### State

| Name | Type | Description |
|------|------|-------------|
| `model` | `Ref<Object>` | Reactive copy of the form's data model |
| `formLoading` | `ComputedRef<Boolean>` | `true` while a submission is in progress |
| `validModel` | `Ref<Boolean\|null>` | Vuetify form validity state (`null` = not yet validated) |

### Methods

| Name | Signature | Description |
|------|-----------|-------------|
| `saveForm` | `() => Promise` | Validate, then submit via `formApi.post/put/patch` |
| `submit` | `() => void` | Trigger form validation and call `saveForm` |
| `handleInput` | `(key, value) => void` | Called by child inputs to update `model[key]` |
| `handleClick` | `(action) => void` | Dispatch a form-level action button click |
| `setSchemaErrors` | `(errors: Object) => void` | Map server validation errors back onto schema fields |
| `resetSchemaErrors` | `() => void` | Clear all server-side error messages from the schema |
| `createSchema` | `(definition) => Object` | Normalize and hydrate a raw schema definition into a resolved schema |

## Watchers

- Watches `props.modelValue` — syncs external model changes into the internal `model`.
- Watches `props.schema` — re-runs `createSchema` when the schema definition changes.

## Server-side Errors

After a failed submission, call `setSchemaErrors(errors)` with the Laravel validation error bag:

```js
setSchemaErrors({
  name: ['The name field is required.'],
  email: ['The email has already been taken.']
})
```

Each field matching a key in `errors` will display the first error message beneath the input.

## See Also

- [useInput](/system-reference/frontend/composables/use-input) — per-input state and `updateModelValue`
- [useFormBase](/system-reference/frontend/composables/use-form-base) — field iteration and layout rendering
- [useValidation](/system-reference/frontend/composables/use-validation) — rule factories
