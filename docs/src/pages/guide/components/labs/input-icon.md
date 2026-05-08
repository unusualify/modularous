---
sidebarPos: 6
sidebarTitle: Input Icon
---

# InputIcon <Badge type="warning" text="experimental" />

`InputIcon` presents a read-only text field that opens a full-screen dialog containing a searchable grid of MDI icons. Clicking an icon saves its `mdi-{name}` string to the model.

## Schema usage

```php
[
  'type'  => 'icon',
  'name'  => 'menu_icon',
  'label' => 'Menu Icon',
]
```

## Value format

The stored value is the full MDI icon string with the `mdi-` prefix, e.g. `"mdi-account"`.

## Behaviour

1. The text field displays the current icon string (read-only).
2. Clicking the field opens a `v-dialog` (max width 700 px, max height 850 px).
3. A search field at the top filters the icon list in real time by substring match.
4. Clicking any icon button stores `mdi-{name}` in the model and closes the dialog.

## Bundled icon set

The component ships with a curated list of ~400 MDI icons covering common categories: account/user, alerts, arrows, calendars, files, formatting, navigation, and more. Icons outside this list are not available through the picker — extend `allIcons` in the component source if additional icons are required.

## Props

| Prop | Type | Default | Description |
|---|---|---|---|
| `label` | `String` | `''` | Label shown on the activator text field |
| `items` | `Array` | `[]` | Unused; reserved for future extension |
| `itemValue` | `String` | `'id'` | Unused; reserved for future extension |
| `itemTitle` | `String` | `'name'` | Unused; reserved for future extension |
| `checkboxColor` | `String` | `'success'` | Unused; reserved for future extension |

## Notes

- The activator text field is `readonly`. The user must open the dialog to change the value.
- No preview of the selected icon is rendered in the text field itself — the raw string (e.g. `mdi-account`) is shown as plain text.
