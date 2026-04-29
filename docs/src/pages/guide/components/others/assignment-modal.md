---
sidebarPos: 1
sidebarTitle: Assignment Modal
---

# AssignmentModal

`AssignmentModal` is a `ue-modal` dialog for creating or editing a task assignment. It presents a validated form with an assignee selector, a due-date picker, a description textarea, and an optional preliminary-documents file upload.

## Usage

```html
<assignment-modal
  v-model="showModal"
  v-model:form="formData"
  :users="userList"
  :loading="saving"
  :filepond="filepond"
  @submit="saveAssignment"
/>
```

```js
const formData = ref({
  assignee_id: null,
  due_at: null,
  description: null,
  preliminaries: [],
})
```

## Props

| Prop | Type | Default | Description |
|---|---|---|---|
| `modelValue` | `Object` | `{}` | Controls modal visibility (`v-model`). |
| `form` | `Object` | `{ assignee_id, due_at, description, preliminaries: [] }` | The form model (`v-model:form`). |
| `users` | `Array` | `[]` | User list for the assignee `v-select`. Each item must have `id` and `name`. |
| `loading` | `Boolean` | `false` | Shows loading state on the submit button. |
| `disabled` | `Boolean` | `false` | Disables the submit button. |
| `variant` | `String` | `'outlined'` | Vuetify input variant applied to all fields. |
| `filepond` | `Object` | `{}` | Schema-style config forwarded to `v-input-filepond` for the preliminary documents field. |
| `minDueDays` | `Number` | `0` | Minimum number of days from today allowed for the due date. Enforces a `futureDateRule`. |

## Emits

| Event | Payload | Description |
|---|---|---|
| `update:modelValue` | `Boolean` | Modal open/close state change |
| `update:form` | `Object` | Updated form model |
| `submit` | — | Emitted when the form passes validation and the **Assign** button is clicked |

## Exposed methods

| Method | Returns | Description |
|---|---|---|
| `validateForm()` | `Promise<{ valid: Boolean }>` | Programmatically validates all fields. Call this before submitting externally. |

## Form fields

| Field | Input type | Validation rules |
|---|---|---|
| Assignee | `v-select` (`item-value: id`, `item-title: name`) | Required |
| Due Date | `v-input-date` | Required, valid date, minimum `minDueDays` days in the future |
| Description | `v-textarea` | Required, minimum 10 characters |
| Preliminary Documents | `v-input-filepond` | Optional; `filepond` prop controls server endpoints and accepted file types |

## Validation

Validation is triggered on `submit` (lazy). The due-date field also validates on `blur`. The form ref is exposed as `validateForm()` for external callers.

## Preliminary documents badge

The preliminary-documents filepond label is wrapped in a `v-badge` with a `mdi-creation` icon to visually indicate it is an AI-assisted or special field.
