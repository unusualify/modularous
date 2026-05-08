---
sidebarPos: 50
sidebarTitle: Overview
sidebarGroupTitle: Others
---

# Others

Miscellaneous components that don't fit a single category — form engine internals, assignment workflow UI, messaging, and data display utilities.

## Components

| Component | Tag | Description |
|---|---|---|
| [`AssignmentModal`](./assignment-modal) | — | Modal form for creating or editing a task assignment |
| [`AssigneeDetails`](./assignee-details) | — | Popover showing assignment details with file upload and action buttons |
| [`ChatMessage`](./chat-message) | — | Single chat message bubble with read/unread state and attachments |
| [`CurrencyNumber`](./currency-number) | `ue-currency-number` | Currency-formatted text field input |
| [`Datatable`](./datatable) | — | Server-side data table with toolbar, filters, and row actions |
| [`FormBase`](./form-base) | `v-form-base` | Refactored schema-driven form engine (current version) |
| [`FormBaseField`](./form-base-field) | — | Internal field renderer for `FormBase` |
| [`CustomFormBase`](./custom-form-base) | `v-custom-form-base` | Original schema-driven form engine (legacy, self-contained) |

## FormBase vs CustomFormBase

`FormBase` and `CustomFormBase` expose the same `v-form-base` API and schema syntax. `FormBase` is the refactored version: its rendering logic is extracted into `useFormBaseLogic` (composable) and `FormBaseField` (sub-component), making it easier to maintain and extend. `CustomFormBase` is the original monolithic implementation and is kept for backwards compatibility.

Use `FormBase` (`v-form-base`) for all new work.
