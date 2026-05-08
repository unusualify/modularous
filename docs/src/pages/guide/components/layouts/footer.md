---
sidebarPos: 6
sidebarTitle: Footer (legacy)
---

# Footer <Badge type="danger" text="legacy" />

`Footer` is a legacy prototype component. It renders a dark `v-footer` with a row of icon-link buttons and placeholder Lorem Ipsum text. It is **not used in production**.

> [!WARNING]
> Do not use this component. It uses Vuetify 2 props (`dark`, `padless`, class-based colour tokens) that are not compatible with Vuetify 3.

## Props

| Prop | Type | Required | Default | Description |
|---|---|---|---|---|
| `items` | `Array` | Yes | — | Array of link objects. Each must have `icon` (MDI icon string) and `url` (href). |
| `show` | `Boolean` | No | `true` | Controls footer visibility via `v-show`. |

## Example items shape

```js
[
  { icon: 'mdi-facebook', url: 'https://facebook.com' },
  { icon: 'mdi-twitter',  url: 'https://twitter.com'  },
]
```

## Notes

- The footer body text is hardcoded Lorem Ipsum and is not configurable.
- The copyright year is rendered dynamically via `new Date().getFullYear()`.
- This component has no connection to the main application store or Modularous config.
