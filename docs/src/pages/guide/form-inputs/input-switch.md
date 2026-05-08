---
sidebarPos: 34
sidebarTitle: Switch
---

# Switch

The `switch` input type renders a toggle switch using Vuetify's `v-switch`. It is functionally similar to [Checkbox](/guide/form-inputs/input-checkbox) but uses a toggle UI instead of a checkbox. The switch is **on by default** (`default: 1`).

## Hydrate

**Class:** `SwitchHydrate`
**Config type:** `switch`
**Output type:** `input-switch` → `VInputSwitch`

The hydrate sets `hideDetails` to `true` and `default` to `1` (on) unless you override them.

## Usage

### Basic switch

```php
[
    'type'  => 'switch',
    'name'  => 'is_active',
    'label' => 'Active',
]
```

### Default off

```php
[
    'type'    => 'switch',
    'name'    => 'is_featured',
    'label'   => 'Featured',
    'default' => 0,
]
```

### Custom true/false values

```php
[
    'type'       => 'switch',
    'name'       => 'status',
    'label'      => 'Status',
    'trueValue'  => 'enabled',
    'falseValue' => 'disabled',
]
```

## Schema Defaults

| Key | Default | Description |
|-----|---------|-------------|
| `color` | `'success'` | Vuetify color for the active (on) state |
| `trueValue` | `1` | Value stored when the switch is on |
| `falseValue` | `0` | Value stored when the switch is off |
| `hideDetails` | `true` | Hide the validation detail row below the switch |
| `default` | `1` | Initial value (on by default) |

> **Switch vs Checkbox:** Both store a boolean-like value. Use `switch` for prominent on/off settings; use `checkbox` for compact lists of flags.

## See Also

- [Checkbox](/guide/form-inputs/input-checkbox) — Same semantics, checkbox UI
- [Hydrates reference](/system-reference/hydrates) — Resolution table and schema contract
