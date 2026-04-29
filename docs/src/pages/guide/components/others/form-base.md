---
sidebarPos: 6
sidebarTitle: Form Base
---

# FormBase (`v-form-base`)

`FormBase` is the refactored schema-driven form engine. It renders a `v-row` of `v-col` items, one per schema key, and delegates all field rendering to [`FormBaseField`](./form-base-field). Business logic lives in the `useFormBaseLogic` composable.

This component shares its API with [`CustomFormBase`](./custom-form-base) — `CustomFormBase` is the original self-contained implementation and is kept for backwards compatibility. Use `FormBase` for all new work.

## Usage

```html
<v-form-base
  id="my-form"
  v-model="model"
  v-model:schema="schema"
  :col="{ cols: 12, md: 6 }"
/>
```

## Props

| Prop | Type | Default | Description |
|---|---|---|---|
| `id` | `String` | `'form-base'` | HTML id and slot namespace prefix |
| `rootId` | `String` | `'form-base'` | Root ID for nested instances |
| `modelValue` | `Object\|Array` | `null` | Form data model (`v-model`) |
| `model` | `Object\|Array` | — | Alias for `modelValue` (legacy support) |
| `schema` | `Object\|Array` | `{}` | Schema definition (`v-model:schema`) |
| `formItem` | `Object` | `{}` | Supplementary data injected into `preview` type fields via `ue-recursive-stuff` |
| `row` | `Object` | — | Vuetify `v-row` props (default: `{ noGutters: false }`) |
| `rowGroup` | `Object` | — | `v-row` props for nested `group`/`array` types |
| `col` | `Object\|Number\|String` | — | Default `v-col` props (overrideable per field via `schema.col`) |
| `colGroup` | `Object\|Number\|String` | — | Default `v-col` props for nested types |
| `flex` | `Object\|Number\|String` | — | Deprecated alias for `col` |
| `noAutoGenerateSchema` | `Boolean` | `false` | Disables automatic schema generation from model keys |

## Emits

| Event | Payload | Description |
|---|---|---|
| `update:modelValue` | `Object\|Array` | Emitted on any field change |
| `update:schema` | `Object\|Array` | Emitted when schema is mutated internally |
| `input` | event object | Low-level input event with full context |
| `update` | event object | Alias; deprecated |
| `resize` | event object | Window resize event |
| `blur` | event object | Field blur event |
| `click` | event object | Field or icon click event |

All emit payloads have the shape:
```js
{
  on: 'input',        // event type
  id: 'my-form',      // form id
  index: null,        // array index (nested forms)
  key: 'user.name',   // dot-notation field key
  value: 'Jane',      // new value
  obj,                // internal field descriptor
  data,               // full current model
  schema,             // full current schema
  parent,             // parent FormBase instance
}
```

## Schema syntax

Each key in `schema` maps to a field definition:

```js
schema = {
  // Shorthand: type string only
  firstName: 'text',

  // Full object
  email: {
    type: 'email',
    label: 'Email Address',
    col: { cols: 12, md: 6 },
    order: 2,
    rules: [(v) => !!v || 'Required'],
  },

  // Nested group
  address: {
    type: 'group',
    title: 'Address',
    schema: {
      street: { type: 'text', label: 'Street' },
      city:   { type: 'text', label: 'City' },
    }
  }
}
```

### Supported schema field keys

| Key | Description |
|---|---|
| `type` | Field type — see [Supported types](#supported-types) |
| `label` | Field label |
| `col` | `v-col` props override for this field |
| `order` | Sort order within the row (ascending) |
| `offset` | `v-col` offset props |
| `hidden` | Hide the field (`v-show`) |
| `spacer` | Inject a `v-spacer` after this field |
| `tooltip` | Tooltip text (string shorthand) or a Vuetify tooltip props object |
| `drag` | Enable drag-and-drop on this field |
| `drop` | Function called when a value is dropped on this field |
| `toCtrl` | `({ value, obj, data, schema }) => value` — transform value going to the control |
| `fromCtrl` | `({ value, obj, data, schema }) => value` — transform value coming from the control |
| `mask` | Input mask string (uses `v-mask` directive) |
| `ext` | Native `<input type>` override (e.g. `'range'`, `'number'`) |
| `typeInt` | Internal component type override (e.g. `'month'` for date pickers) |
| `translated` | When `true`, renders via `v-input-locale` for multi-language input |
| `cascade` | Key of the dependent select to update when this select changes |
| `autofill` | Array of field keys to autofill from the selected item's data |
| `searchInput` | Enables `v-model:search-input` binding |

### Supported types

`text`, `email`, `password`, `number`, `textarea`, `select`, `autocomplete`, `combobox`, `checkbox`, `switch`, `radio`, `slider`, `range` (via `ext`), `date`, `time`, `color`, `file`, `img`, `icon`, `btn`, `btn-toggle`, `list`, `array`, `group`, `wrap`, `treeview`, `title`, `preview`, `dynamic-component`, plus any custom type registered via `registerInputType`.

## Slots

FormBase generates slot names dynamically from the form `id` and field `key`. Separator: `-`.

### Form-level slots

| Slot name | Description |
|---|---|
| `slot-top-{id}` | Rendered at the very top of the row |
| `slot-bottom-{id}` | Rendered at the very bottom of the row |

### Field-level slots (replace / wrap individual fields)

All bindings include `{ obj, index, id }`.

| Slot name | Description |
|---|---|
| `slot-top-key-{id}-{key}` | Above the field column |
| `slot-item-key-{id}-{key}` | Replaces the field entirely |
| `slot-bottom-key-{id}-{key}` | Below the field column |
| `slot-top-type-{id}-{type}` | Above all fields of this type |
| `slot-item-type-{id}-{type}` | Replaces all fields of this type |
| `slot-bottom-type-{id}-{type}` | Below all fields of this type |

### Inject slots (inside components, e.g. `append`, `prepend`, `thumb-label`)

```
slot-inject-{verb}-key-{id}-{key}
```

Example — custom `append-inner` on the `email` field of form `my-form`:
```html
<template #slot-inject-append-inner-key-my-form-email>
  <v-icon>mdi-email</v-icon>
</template>
```

### Tooltip slot

```
tooltip                  (default, matches all keys)
slot-tooltip-key-{id}-{key}
```

### Array slots

| Slot name | Description |
|---|---|
| `slot-top-array-{id}-{key}` | Above each array item |
| `slot-item-array-{id}-{key}` | Replaces each array item |
| `slot-bottom-array-{id}-{key}` | Below each array item |

Array item bindings: `{ obj, id, index, idx, item }`.

## Schema rebuilding

`FormBase` calls `rebuildArrays(model, schema)` on `beforeMount` and whenever the top-level keys of `modelValue` change (detected via `JSON.stringify(Object.keys(__dot(value)))`). This flattens the nested model and schema into a single sorted array for rendering.

If `schema` is empty and `noAutoGenerateSchema` is `false`, a minimal schema is auto-generated from the model's value types.
