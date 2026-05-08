---
sidebarPos: 3
sidebarTitle: Stepper Summary
---

# StepperSummary

`StepperSummary` is the right-column sidebar panel. During regular steps it shows per-step preview cards and a **Next** button. On the final step it renders the `summary.final` slot — by default a `StepperFinalSummary` card.

> [!NOTE]
> This is an internal sub-component of `ue-stepper-form`. You do not use it directly.

## Behaviour

- When `isLastStep` is `false`, iterates over `forms` and renders either a custom `summary-form-{n}` slot or the default `ue-form-summary-item` for each step whose preview model is filled.
- Dividers are injected between filled summary items automatically.
- The **Next** button at the bottom emits `next-form` with `activeStep - 1` (0-based index), triggering validation in the parent.
- When `isLastStep` is `true`, the `summary.final` slot is rendered. The default implementation renders [`StepperFinalSummary`](./stepper-final-summary) and wires `complete-form` to its complete button.

## Props

| Prop | Type | Required | Description |
|---|---|---|---|
| `isLastStep` | `Boolean` | Yes | `true` when `activeStep > forms.length`. |
| `forms` | `Array` | Yes | The `forms` array from the parent stepper. |
| `activeStep` | `Number` | Yes | Currently active 1-based step. |
| `models` | `Array` | Yes | Array of per-step model objects. |
| `schemas` | `Array` | Yes | Array of per-step schema arrays. |
| `previewModel` | `Array` | Yes | Array of per-step preview data objects. |
| `previewTitles` | `Array` | Yes | Resolved title string for each step's summary card. |
| `isPreviewModelFilled` | `Function` | Yes | `(index: number) => boolean` — determines whether a step has preview data to display. |

## Emits

| Event | Payload | Description |
|---|---|---|
| `next-form` | `Number` | 0-based index of the current step. Triggers form validation and advance. |
| `complete-form` | — | Emitted when the final-step complete button is clicked. |

## Slots

### `summary.forms`

Replaces the entire default per-step summary list (all steps at once):

```html
<template #summary.forms>
  <!-- fully custom summary list -->
</template>
```

### `summary-form-{n}`

Replaces the default summary card for step `n` (1-based). Receives a scoped object:

```html
<template #summary-form-2="{ title, model, schema, previewModel, index, order, length }">
  <p>{{ title }}: {{ model.company }}</p>
</template>
```

| Binding | Type | Description |
|---|---|---|
| `index` | `Number` | Zero-based step index |
| `order` | `Number` | One-based step number |
| `title` | `String` | Resolved preview title |
| `model` | `Object` | Form model for this step |
| `schema` | `Array` | Form schema for this step |
| `previewModel` | `Object` | Preview model for this step |
| `isPreviewModelFilled` | `Function` | `(index) => Boolean` |
| `length` | `Number` | Total number of steps |

### `summary.final`

Replaces the entire right-column content on the final step. Receives:

```html
<template #summary.final="{ model, schema, previewModel, onComplete }">
  <!-- custom final panel, call onComplete() to submit -->
</template>
```

| Binding | Type | Description |
|---|---|---|
| `model` | `Array` | All step models |
| `schema` | `Array` | All step schemas |
| `previewModel` | `Array` | All preview models |
| `onComplete` | `Function` | Call to trigger form submission |
