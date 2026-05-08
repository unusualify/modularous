---
sidebarPos: 8
sidebarTitle: Callout
---

# Callout <Badge type="warning" text="experimental" />

`Callout` is a bordered alert card that presents a **title** on the left and a larger **value** on the right. It is built on top of `v-alert` with a 4 px start border and no outer border, giving it a clean callout / stat-card appearance.

## Usage

```html
<ue-callout
  title="Total Revenue"
  value="$12,400"
  color="success"
  bg-color="white"
/>
```

## Props

| Prop | Type | Default | Description |
|---|---|---|---|
| `title` | `String` | `''` | Left-column label text |
| `value` | `String` | `''` | Right-column value text (rendered at 1.5 rem) |
| `color` | `String` | `'success'` | Border colour and default text colour (any Vuetify colour token) |
| `bgColor` | `String` | `'white'` | Background colour of the alert card |
| `textColor` | `String` | `''` | Explicit text colour override. Falls back to `color` when empty. |

## Layout

The component uses `RowFormat` internally to render a two-column row:

| Column | Width | Content |
|---|---|---|
| Left | 8 / 12 | `title` — vertically centred, rendered at body size |
| Right | 4 / 12 | `value` — rendered at 1.5 rem font size |

Both columns inherit the resolved text colour (either `textColor` or `color`).

## Colour logic

```
textColor prop set? → use textColor
textColor prop empty? → use color
```

## CSS class

The component adds `.v-callout` to the alert element. The accompanying scoped style sets:
- `border: unset` (removes the default Vuetify outer border)
- `--v-border-opacity: 1` (forces full opacity on the start border)
- `border-inline-start-width: 4px`

## Example — multiple callouts

```html
<ue-callout title="Subscribers"  value="8,240"  color="primary" />
<ue-callout title="Impressions"  value="142,000" color="info"    />
<ue-callout title="Conversions"  value="3.2%"   color="warning" />
```
