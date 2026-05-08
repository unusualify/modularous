---
sidebarPos: 1
sidebarTitle: Stepper Header
---

# StepperHeader

`StepperHeader` renders the horizontal step indicator bar at the top of the stepper. It displays one item per form step plus a final **Preview & Summary** item. Completed steps show a checkmark and are clickable to navigate back.

> [!NOTE]
> This is an internal sub-component of `ue-stepper-form`. You do not use it directly.

## Behaviour

- Each step item shows its `title` from the `forms` array.
- A step is marked **complete** when `activeStep` is greater than that step's index.
- Clicking a completed step's title emits `step-click` with the 1-based step number. The parent (`StepperForm`) only allows backward navigation — forward jumps to incomplete steps are blocked.
- The final **Preview & Summary** item is always the last entry and has no click handler.

## Props

| Prop | Type | Required | Description |
|---|---|---|---|
| `forms` | `Array` | Yes | The same `forms` array passed to `ue-stepper-form`. Each entry must have a `title`. |
| `activeStep` | `Number` | Yes | The currently active 1-based step index. |

## Emits

| Event | Payload | Description |
|---|---|---|
| `step-click` | `Number` | 1-based index of the step the user clicked. Only emitted for completed steps (backward navigation). |

## Visual States

| Condition | Appearance |
|---|---|
| `activeStep > i+1` | Step is complete — checkmark icon, title highlighted in primary colour |
| `activeStep === i+1` | Step is active — title highlighted in primary colour, bold |
| `activeStep < i+1` | Step is upcoming — default muted appearance |
