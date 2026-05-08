---
sidebarPos: 4
sidebarTitle: Stepper Preview
---

# StepperPreview

`StepperPreview` is the main-column content for the final step. It shows two areas:

1. **Formatted preview cards** — a responsive grid of `ue-configurable-card` tiles built from `previewNotations`.
2. **Final form selectable cards** — fetched items from `finalFormFields` that the user can add or remove from the payload before submitting.

> [!NOTE]
> This is an internal sub-component of `ue-stepper-form`. You do not use it directly.

## Behaviour

- `formattedPreview` is passed in already-formatted from `StepperForm` via `NotationUtil.formattedPreview`.
- Each card in `previewFormData` (fetched via `finalFormFields[n].endpoint`) renders a `ue-configurable-card` with a toggle button (`mdi-plus` / `mdi-minus`).
- Clicking the toggle button emits `final-form-action` with `{ index, event }` where `event` is the new boolean selected state.
- Items present in `protectedLastStepModel` are rendered as read-only (the toggle is disabled) — this is used when editing an existing record and some items were already committed.
- Card appearance: selected items get `bg-primary`; unselected items get `bg-grey-lighten-5`.

## Props

| Prop | Type | Required | Default | Description |
|---|---|---|---|---|
| `formattedPreview` | `Array` | No | `[]` | Pre-formatted card data for the summary grid (top section). Each entry is a `ue-configurable-card` props object. |
| `previewFormData` | `Array` | No | `[]` | Fetched selectable items for the final form section (bottom section). |
| `lastStepModel` | `Object` | Yes | — | Object holding currently selected values, keyed by `fieldName`. |
| `protectedLastStepModel` | `Object` | No | `{}` | Initial model values that should be read-only. |
| `finalFormTitle` | `String` | Yes | — | Heading above the selectable cards. |
| `finalFormSubtitle` | `String` | No | `null` | Sub-heading below `finalFormTitle`. |

## Emits

| Event | Payload | Description |
|---|---|---|
| `final-form-action` | `{ index: Number, event: Boolean }` | Emitted when a card's toggle button is clicked. `index` is the item's position in `previewFormData`; `event` is `true` (add) or `false` (remove). |

## Card Item Structure

Each entry in `previewFormData` is a fetched and transformed item. The relevant fields used by `StepperPreview`:

| Field | Description |
|---|---|
| `fieldName` | Key in `lastStepModel` where the selection is stored |
| `form_card_items` | Array of values for each card column (name, description, tags, price, etc.) |
| `_fieldFormat` | `'id'` or an object map — controls how selected values are stored |
| `_fieldFormatSourceKey` | Key on the item used as the source for uniqueness checks (default `'id'`) |
| `_fieldFormatUniqueKey` | Key used when matching against object-format stored values (default `'id'`) |

These fields are injected automatically by `StepperForm.handlePreviewFormField` based on the `finalFormFields` configuration.

## Selection Logic

| `_fieldFormat` value | Add behaviour | Remove behaviour |
|---|---|---|
| `String` (e.g. `'id'`) | Appends `item[fieldFormat]` to the array | Filters the value out of the array |
| `Object` (field map) | Builds a new object from the map and appends it | Finds by `_fieldFormatUniqueKey` and splices it out |
