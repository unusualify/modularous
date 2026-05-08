---
sidebarPos: 9
sidebarTitle: Row Format
---

# RowFormat <Badge type="warning" text="experimental" />

`RowFormat` renders a single `v-row` containing one `v-col` per element in the `elements` array. Each column holds a `<label>` with configurable text, class, and style. A shared `color` prop applies a Vuetify text-colour class to every label.

It is used internally by [`Callout`](./callout) to lay out its title/value columns.

## Usage

```html
<ue-row-format
  :elements="[
    { text: 'Revenue',  col: { cols: 8 } },
    { text: '$12,400',  col: { cols: 4 }, style: { 'font-size': '1.5rem' } },
  ]"
  color="success"
/>
```

## Props

| Prop | Type | Default | Description |
|---|---|---|---|
| `elements` | `Array` | `[]` | Array of column definitions. See [Element object](#element-object). |
| `color` | `String` | `''` | Vuetify colour token applied as `text-{color}` to every label. Empty string applies no class. |

## Element object

Each entry in `elements` is a plain object:

| Key | Type | Required | Description |
|---|---|---|---|
| `text` | `String` | Yes | Label text content |
| `col` | `Object` | No | Props forwarded to `v-col` (e.g. `{ cols: 6, md: 4 }`) |
| `class` | `String` | No | Additional CSS classes on the `<label>` |
| `style` | `Object` | No | Inline styles on the `<label>` (e.g. `{ 'font-size': '1.5rem' }`) |

## Colour logic

When `color` is set to a non-empty string the class `text-{color}` is added to every `<label>`. Individual columns cannot override this — use the `class` key on an element only for non-colour classes.
