---
sidebarPos: 4
sidebarTitle: Overview
sidebarGroupTitle: Stepper Form
---

# Stepper Form <Badge type="tip" text="^0.9.2" />

The `ue-stepper-form` component provides multi-step forms with a built-in summary sidebar, step navigation, validation, and a final preview step. Each step behaves like a standard `ue-form`, with all schema-driven field types supported.

## Architecture

The stepper is composed of five internal sub-components that work together:

| Component | Role |
|---|---|
| [`StepperHeader`](./stepper-header) | Step indicator bar at the top |
| [`StepperContent`](./stepper-content) | Main form area (left column) |
| [`StepperSummary`](./stepper-summary) | Summary sidebar (right column) |
| [`StepperPreview`](./stepper-preview) | Final step preview with selectable cards |
| [`StepperFinalSummary`](./stepper-final-summary) | Final summary card shown in the sidebar on the last step |

```
┌──────────────── StepperHeader ────────────────┐
│  Step 1   Step 2   Step 3   Preview & Summary │
└───────────────────────────────────────────────┘
┌────────────────────────┐ ┌────────────────────┐
│                        │ │                    │
│   StepperContent       │ │  StepperSummary    │
│   (ue-form per step)   │ │  (per-step or      │
│                        │ │   final summary)   │
│   StepperPreview       │ │                    │
│   (on final step)      │ │  StepperFinalSummary│
│                        │ │  (on final step)   │
└────────────────────────┘ └────────────────────┘
```

## Basic Usage

```php
@php
  $forms = [
    [
      'title'        => 'Step 1 Title',
      'id'           => 'stepper-form-1',
      'previewTitle' => 'Custom preview card title',
      'schema'       => $this->createFormSchema([
        ['type' => 'text',  'name' => 'first_name'],
        ['type' => 'email', 'name' => 'email'],
      ]),
    ],
    [
      'title'  => 'Step 2 Title',
      'schema' => $this->createFormSchema([
        ['type' => 'text', 'name' => 'company'],
      ]),
    ],
  ];
@endphp

<ue-stepper-form
  :forms="@json($forms)"
  action-url="/api/submit"
  redirect-url="/dashboard"
/>
```

> [!IMPORTANT]
> This component was introduced in [v0.9.2]

## Props

| Prop | Type | Default | Description |
|---|---|---|---|
| `forms` | `Array` | `[]` | Array of form definitions. Each entry must have a `title` and `schema`. See [Form Definition](#form-definition). |
| `modelValue` | `Object` | `{}` | Initial model values, merged into each form's model on mount. |
| `actionUrl` | `String` | — | Endpoint for the final form submission (`POST` or `PUT` if `modelValue.id` is set). |
| `redirectUrl` | `String` | `null` | URL to navigate to after successful submission. |
| `currentStep` | `Number` | `1` | Step to start on. |
| `preview` | `Array` | `[]` | Initial `previewModel` values (per-step preview data). |
| `isEditing` | `Boolean` | `false` | Passed down to each `ue-form` to enable edit mode. |
| `flexBreakpoint` | `String` | `null` | Breakpoint at which the stepper switches to flex layout (`sm` \| `md` \| `lg` \| `xl` \| `xxl`). |
| `cardsNotation` | `String` | `'models.1.pressReleasePackages'` | Dot-notation path to extract summary card data. |
| `summaryNotations` | `Array\|Object` | `{}` | Notations that control what appears in the step summary sidebar. |
| `previewNotations` | `Array\|Object` | `[]` | Notations that control the formatted preview cards on the final step. |
| `finalFormTitle` | `String` | `null` | Title shown above the selectable cards on the final preview step. |
| `finalFormSubtitle` | `String` | `null` | Subtitle shown below `finalFormTitle`. |
| `finalFormFields` | `Array` | `[]` | Defines selectable fields on the final step. See [Final Form Fields](#final-form-fields). |
| `protectInitialValue` | `Boolean` | `false` | When `true`, pre-selected items from `modelValue` are read-only on the final step. |
| `validationScrollingDuration` | `Number` | `1000` | Scroll animation duration (ms) when auto-scrolling to a validation error. |
| `validationScrollingEasing` | `String` | `'easeInOutCubic'` | Easing function for validation scroll. |
| `validationScrollingOffset` | `Number` | `0` | Pixel offset applied during validation scroll. |
| `responseModalIcon` | `String` | `'mdi-check-circle-outline'` | Icon shown in the success modal. |
| `responseModalTitle` | `String` | `$t('Request Complete')` | Title shown in the success modal. |
| `responseModalMessage` | `String` | `$t('Congratulations!...')` | Body text shown in the success modal. |
| `responseModalButtonText` | `String` | `'Ok'` | Button label in the success modal. |
| `responseModalOptions` | `Object` | `{}` | Extra props forwarded to the `ue-modal` success dialog. |

## Form Definition

Each element of the `forms` array is a plain object:

| Key | Type | Required | Description |
|---|---|---|---|
| `title` | `String` | Yes | Label shown in the step header and summary. |
| `schema` | `Array` | Yes | Schema produced by `createFormSchema(...)`. |
| `id` | `String` | No | HTML `id` for the step's form element. |
| `previewTitle` | `String` | No | Overrides `title` for the summary preview card header. |
| `summaryTitle` | `String` | No | Overrides `title` specifically for the sidebar summary. |
| `summarySearchHaystack` | `String` | No | `'model'` (default) or `'schema'` — where to resolve the summary title. |
| `summarySearchInput` | `String` | No | Input name used to resolve a dynamic schema-based summary title. |
| `fullWidth` | `Boolean` | No | When `true`, the form takes full width and the summary sidebar is hidden. |

## Slots

### `summary-form-{n}`

Replaces the default summary card for step `n` (1-based). Receives a scoped object:

```html
<template #summary-form-1="{ title, model, schema, previewModel, index, order, length }">
  <div>Custom summary for step 1</div>
</template>
```

| Binding | Description |
|---|---|
| `index` | Zero-based step index |
| `order` | One-based step number |
| `title` | Resolved preview title for this step |
| `model` | Form model for this step |
| `schema` | Form schema for this step |
| `previewModel` | Preview model for this step |
| `isPreviewModelFilled` | Function: `(index) => Boolean` |
| `length` | Total number of steps |

### `summary.final`

Replaces the entire final-step summary panel. Receives:

```html
<template #summary.final="{ model, schema, previewModel, completeForm }">
  <!-- custom final summary -->
</template>
```

### `summary.final.body`

Injects content into the body section of the default `StepperFinalSummary` card:

```html
<template #summary.final.body="{ models, schemas, lastStepModel, finalFormFields, lastFormPreview }">
  <!-- line items, pricing breakdown, etc. -->
</template>
```

### `summary.final.total.label` / `summary.final.total`

Override the "Total" label and value in the final summary:

```html
<template #summary.final.total.label>Price</template>
<template #summary.final.total="{ payload }">
  {{ payload.amount_formatted }}
</template>
```

### `summary.final.description`

Override the description text below the total:

```html
<template #summary.final.description>
  Prices are exclusive of VAT.
</template>
```

## Final Form Fields

`finalFormFields` defines selectable items shown on the final preview step. Each entry is either a dot-notation string or a configuration object:

```js
finalFormFields: [
  {
    modelNotation: 'models.0.package_id',   // dot-path into models
    fieldName:     'selected_packages',      // key written into the payload
    endpoint:      '/api/packages',          // fetches available options
    notation:      'packages',               // key inside each API response item
    afterStep:     1,                        // fetch after leaving step 1
    cardFields:    ['name', 'description', 'tags'],
    format:        'id',                     // 'id' or an object map
    formatSourceKey: 'id',
    formatUniqueKey: 'id',
  }
]
```

| Key | Type | Description |
|---|---|---|
| `modelNotation` | `String` | Dot-path into `models` whose value provides the selected IDs. |
| `fieldName` | `String` | Key written to the final payload. Defaults to the last segment of `modelNotation`. |
| `endpoint` | `String` | API endpoint to fetch available items. Receives `?ids[]=...` for new IDs. |
| `notation` | `String` | Property inside each fetched item that contains the selectable sub-items. |
| `afterStep` | `Number` | Step number after which the fetch is triggered (on step advance). |
| `cardFields` | `Array` | Fields from the item to display in the card. Nested arrays create grouped cells. |
| `format` | `String\|Object` | `'id'` stores IDs; an object maps payload keys to item fields. |
| `formatSourceKey` | `String` | Source key on the fetched item for uniqueness checks (default `'id'`). |
| `formatUniqueKey` | `String` | Key used when matching against the stored object array (default `'id'`). |

## Full-Width Steps

Set `fullWidth: true` on a form definition to make that step span the full container width while hiding the summary sidebar. Navigation uses a `v-stepper-actions` bar at the bottom instead.

```js
{
  title: 'Wide Step',
  fullWidth: true,
  schema: $this->createFormSchema([...]),
}
```
