---
sidebarPos: 2
sidebarTitle: Stepper Content
---

# StepperContent

`StepperContent` is the left-column panel that houses one `ue-form` per step and a custom preview slot on the final step. It manages local copies of models and schemas to keep updates reactive without unnecessary re-renders.

> [!NOTE]
> This is an internal sub-component of `ue-stepper-form`. You do not use it directly.

## Behaviour

- Renders a `v-stepper-window-item` for each entry in `forms`, each containing a `ue-form` bound to the corresponding model and schema.
- Forms are mounted lazily: a form only renders once `activeStep > i` (i.e. the user has reached or passed that step).
- On the final step (`value = forms.length + 1`) the `preview` slot is rendered instead of a form.
- The component keeps internal `models` and `localSchemas` clones, syncing them with the parent via watchers and emitting `update:modelValue` / `update:schemas` when they change.
- The scrollable window (`#ue-stepper-content-window`) respects `maxHeight` minus `coverHeight` so full-width steps can offset for the bottom action bar.

## Props

| Prop | Type | Required | Default | Description |
|---|---|---|---|---|
| `modelValue` | `Array` | Yes | — | Array of per-step model objects. |
| `forms` | `Array` | Yes | — | The `forms` array from the parent stepper. |
| `schemas` | `Array` | Yes | `[]` | Array of per-step schema arrays. |
| `activeStep` | `Number` | Yes | — | The currently active 1-based step. |
| `formRefs` | `Array` | Yes | — | Array of `ref` handles, one per step, used by the parent for programmatic validation. |
| `isEditing` | `Boolean` | No | `false` | Passed to each `ue-form` to enable edit mode. |
| `maxHeight` | `String` | No | `'84vh'` | CSS value for the scrollable window's maximum height. |
| `coverHeight` | `Number` | No | `0` | Pixel height to subtract from `maxHeight` (e.g. bottom action bar height). |

## Emits

| Event | Payload | Description |
|---|---|---|
| `update:modelValue` | `Array` | Emitted when any step's model changes. |
| `update:schemas` | `Array` | Emitted when any step's schema changes. |
| `form-input` | `{ event, index }` | Forwarded from each `ue-form`'s `input` event; `index` is the 0-based step index. |
| `form-valid` | `{ event, index }` | Forwarded from each `ue-form`'s `update:valid` event; `event` is `true/false`. |

## Slots

| Slot | Description |
|---|---|
| `preview` | Rendered inside the final step's `v-stepper-window-item`. Used by `StepperForm` to inject `StepperPreview`. |
