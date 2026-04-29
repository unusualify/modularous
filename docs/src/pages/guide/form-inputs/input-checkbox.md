---
sidebarPos: 6
sidebarTitle: Checkbox
---

# Checkbox

The `checkbox` input type renders a single boolean toggle using Vuetify's `v-checkbox`. It stores a truthy/falsy value and is appropriate for boolean flags on a model.

## Hydrate

**Class:** `CheckboxHydrate`
**Config type:** `checkbox`
**Output type:** `checkbox` (Vuetify `v-checkbox`)

## Usage

### Basic checkbox

```php
[
    'type'  => 'checkbox',
    'name'  => 'is_active',
    'label' => 'Active',
]
```

### Custom true/false values

```php
[
    'type'       => 'checkbox',
    'name'       => 'is_featured',
    'label'      => 'Featured',
    'trueValue'  => 'yes',
    'falseValue' => 'no',
]
```

### Custom color

```php
[
    'type'  => 'checkbox',
    'name'  => 'is_published',
    'label' => 'Published',
    'color' => 'primary',
]
```

## Schema Defaults

| Key | Default | Description |
|-----|---------|-------------|
| `color` | `'success'` | Vuetify color for the checked state |
| `trueValue` | `1` | Value stored when the checkbox is checked |
| `falseValue` | `0` | Value stored when the checkbox is unchecked |
| `hideDetails` | `true` | Hide validation detail row below the checkbox |
| `default` | `0` | Initial value (always set to `0` by the hydrate) |

## See Also

- [Hydrates reference](/system-reference/hydrates) — Resolution table and schema contract
