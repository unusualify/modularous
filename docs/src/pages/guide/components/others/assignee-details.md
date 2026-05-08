---
sidebarPos: 2
sidebarTitle: Assignee Details
---

# AssigneeDetails

`AssigneeDetails` renders a `v-menu` popup that displays the full details of an assignment. The activator is a `v-list` item showing the assignee's name and avatar. Clicking it opens a card with the task summary, description, preliminary documents, submitted attachments, and (when the viewer is the assignee) a file upload area and action buttons.

## Usage

```html
<assignee-details
  :assignment="assignment"
  :formatted-assignment="formattedAssignment"
  :is-assignee="currentUser.id === assignment.assignee_id"
  :is-authorized="currentUser.canManageAssignments"
  :filepond="filepond"
  v-model:attachments="attachments"
  v-model:attachments-loading="attachmentsLoading"
  @click:complete="completeAssignment"
  @click:save="saveAttachments"
/>
```

## Props

| Prop | Type | Default | Description |
|---|---|---|---|
| `assignment` | `Object` | `{}` | Raw assignment data. Uses `description`, `preliminaries`, `attachments`, and `status`. |
| `formattedAssignment` | `Object` | `{}` | Pre-formatted display object. Used for the activator list and the card header. Must include `prependAvatar`, `assigneeName`, `title`, and `subDescription`. |
| `isAssignee` | `Boolean` | `false` | When `true`, shows the file upload field and the Complete/Save action buttons. |
| `isAuthorized` | `Boolean` | `false` | When `true`, shows the assignee activator list and (when not the assignee) the submitted attachments list. |
| `filepond` | `Object` | `null` | Filepond config forwarded to `v-input-filepond`. The `type` key is stripped before forwarding. Shown only when `isAssignee` is `true`. |
| `attachments` | `Array` | `[]` | Two-way bound (`v-model:attachments`) list of uploaded file objects. |
| `attachmentsLoading` | `Boolean` | `false` | Two-way bound (`v-model:attachments-loading`). Set to `true` while files are uploading. |
| `loading` | `Boolean` | `false` | Loading state applied to the Save button. |

## Emits

| Event | Payload | Description |
|---|---|---|
| `update:attachments` | `Array` | Emitted when the attachments model changes |
| `update:attachmentsLoading` | `Boolean` | Emitted when file loading state changes |
| `click:complete` | — | Emitted when the **Complete** button is clicked |
| `click:save` | `{ attachments: Array }` | Emitted when the **Save** button is clicked (only when `attachments.length > 0`) |

## Card sections

| Section | Visibility | Content |
|---|---|---|
| Task summary header | Always | `formattedAssignment.title` + `subDescription` via `v-list` |
| Description | Always | `assignment.description` with `mdi-information-outline` icon |
| Preliminary documents | When `assignment.preliminaries.length > 0` | File preview via `ue-filepond-preview` |
| Submitted attachments | `isAuthorized && !isAssignee && attachments.length > 0` | File preview via `ue-filepond-preview` |
| File upload | `isAssignee` | `v-input-filepond` bound to `attachments`; disabled when `assignment.status !== 'pending'` |
| Action buttons | `isAssignee` | **Complete** (disabled if not pending) and **Save** (disabled if no attachments or not pending) |

## Status gating

Both the file upload and the action buttons are disabled when `assignment.status !== 'pending'`. This prevents modifications to completed or cancelled assignments.
