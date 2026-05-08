---
sidebarPos: 4
sidebarTitle: Input Color
---

# InputColor <Badge type="warning" text="experimental" />

`InputColor` renders a read-only text field that displays the stored hex value. An inlined colour swatch acts as a `v-menu` activator — clicking it opens a `v-color-picker` popover.

## Schema usage

```php
[
  'type'  => 'color',
  'name'  => 'brand_color',
  'label' => 'Brand Color',
]
```

## Value format

The stored value is a hex colour string including the `#` prefix, e.g. `#1A73E8`. The input mask `!XNNNNNNNN` enforces this format in the text field (case-insensitive, up to 9 characters).

## Swatch behaviour

| State | Swatch appearance |
|---|---|
| Picker closed | Square with 4px border-radius |
| Picker open | Circle (50% border-radius) |

The transition between shapes is animated over 200 ms (`ease-in-out`).

## Notes

- The text field is `readonly`. The user can only select a colour via the picker; typing is blocked.
- Schema keys are forwarded to both the `v-text-field` and the `v-color-picker` via `obj.schema` — set `hide-inputs: true` on the schema if you want to suppress the hex/RGB input rows in the picker.
- No alpha channel support is configured by default. Pass `mode: 'hexa'` on the schema to enable it.
