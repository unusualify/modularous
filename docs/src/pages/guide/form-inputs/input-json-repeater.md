---
sidebarPos: 22
sidebarTitle: Json Repeater
---

# Json Repeater

The `json-repeater` input type is a thin alias over [Repeater](/guide/form-inputs/input-repeater). It renders `VInputRepeater` with `root` set to `'json-repeater'`, which tells the repeater to serialise each row as a JSON object rather than treating it as a relational record.

## Hydrate

**Class:** `JsonRepeaterHydrate` (extends `RepeaterHydrate`)
**Config type:** `json-repeater`
**Output type:** `input-repeater` → `VInputRepeater`

`JsonRepeaterHydrate` is a single-line subclass of `RepeaterHydrate` with no overrides. The only effective difference is that the resolved `root` value becomes `'json-repeater'`, signalling to the frontend that rows should be JSON-serialised.

## Usage

```php
[
    'type'         => 'json-repeater',
    'name'         => 'addresses',
    'label'        => 'Addresses',
    'singularLabel'=> 'Address',
    'schema'       => [
        [
            'type'  => 'text',
            'name'  => 'street',
            'label' => 'Street',
        ],
        [
            'type'  => 'text',
            'name'  => 'city',
            'label' => 'City',
        ],
    ],
]
```

## Schema Defaults

Inherits all defaults from `RepeaterHydrate`:

| Key | Default | Description |
|-----|---------|-------------|
| `root` | `'json-repeater'` | Tells the frontend to serialise rows as JSON |
| `col.cols` | `12` | Always full-width |
| `singularLabel` | (field label) | Label used on the "Add" button |

## See Also

- [Repeater](/guide/form-inputs/input-repeater) — Base repeater for relational rows
- [Json](/guide/form-inputs/input-json) — Single JSON group (non-repeating)
- [Hydrates reference](/system-reference/hydrates) — Resolution table and schema contract
