---
sidebarTitle: useTableForms
---

# useTableForms

Manages the create/edit form panel inside a data table — open/close state, loading, errors, and the custom-form-modal workflow.

**File:** `vue/src/js/hooks/table/useTableForms.js`

---

## Props Factory

```js
import { makeTableFormsProps } from '@/hooks/table/useTableForms'
```

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `inputFields` | `Array` | `[]` | Legacy override for the form's field list |
| `formSchema` | `Object` | required | Schema definition for the table's create/edit form |
| `formWidth` | `String\|Number` | `'60%'` | Width of the form modal |
| `createOnModal` | `Boolean` | `true` | Open the create form in a modal |
| `editOnModal` | `Boolean` | `true` | Open the edit form in a modal |
| `embeddedForm` | `Boolean` | `false` | Embed the form inline instead of in a modal |
| `addBtnOptions` | `Object` | `{}` | Props for the "Add" button (e.g. custom text) |
| `noForm` | `Boolean` | `false` | Disable create/edit form entirely |
| `formActions` | `Array\|Object` | `[]` | Extra action buttons in the form footer |

## Usage

```js
import useTableForms, { makeTableFormsProps } from '@/hooks/table/useTableForms'

const {
  formActive,
  UeForm,
  formLoading,
  formErrors,
  addBtnTitle,
  openForm,
  closeForm,
  createForm,
  handleFormSubmission,
} = useTableForms(props, context)
```

## Returns

### State

| Name | Type | Description |
|------|------|-------------|
| `formActive` | `Ref<Boolean>` | Whether the create/edit form is visible |
| `UeForm` | `Ref` | Template ref to the `<UeForm>` component |
| `customFormModalActive` | `Ref<Boolean>` | Whether the custom action-form modal is open |
| `customFormSchema` | `Ref<Object>` | Schema for the custom action form |
| `customFormModel` | `Ref<Object>` | Model for the custom action form |
| `customFormAttributes` | `Ref<Object>` | Attributes for the custom action form |
| `customFormModalAttributes` | `Ref<Object>` | Modal attributes for the custom action form |

### Computed

| Name | Type | Description |
|------|------|-------------|
| `formRef` | `ComputedRef<String>` | Unique DOM ref name for the form |
| `formStyles` | `ComputedRef<Object>` | `{ width: formWidth }` style object |
| `formLoading` | `ComputedRef<Boolean>` | `true` while the form is submitting |
| `formErrors` | `ComputedRef<Object>` | Current validation errors from the Vuex form store |
| `formIsValid` | `ComputedRef<Boolean\|null>` | Form validity state from `UeForm` |
| `addBtnTitle` | `ComputedRef<String>` | Translated "Add {item}" label for the create button |

### Methods

| Name | Signature | Description |
|------|-----------|-------------|
| `openForm` | `() => void` | Open the create/edit form |
| `closeForm` | `() => void` | Close the form and reset the edited item |
| `createForm` | `() => void` | Reset the edited item to defaults and open the form |
| `handleFormSubmission` | `(data) => void` | Called on form submit response — closes form and reloads table on success |

## Notes

- Closing the form automatically calls `resetEditedItem()` via a watcher on `formActive`.
- `handleFormSubmission` checks `data.variant === 'success'` before triggering a reload.

## See Also

- [useTableItem](/system-reference/frontend/composables/table/use-table-item) — edited item state
- [useTableItemActions](/system-reference/frontend/composables/table/use-table-item-actions) — triggers `openForm` for edit/duplicate
- [useTable](/system-reference/frontend/composables/use-table) — orchestrating composable
