---
sidebarPos: 10
sidebarTitle: Combobox
---

# Combobox

The `combobox` input type renders Vuetify's `v-combobox`, which allows users to either select an existing option or type in a free-form value. Like `autocomplete`, it supports static items, remote data via `connector`, and scroll mode for large datasets.

## Hydrate

**Class:** `ComboboxHydrate`
**Config type:** `combobox`
**Output type:** `combobox` or `input-select-scroll` (scroll mode)

## Usage

### Static items

```php
[
    'type'      => 'combobox',
    'name'      => 'tags',
    'label'     => 'Tags',
    'items'     => ['php', 'laravel', 'vue'],
    'multiple'  => true,
]
```

### Remote items via connector

```php
[
    'type'      => 'combobox',
    'name'      => 'category',
    'label'     => 'Category',
    'connector' => 'Blog:Category|repository:list',
]
```

### Scroll mode (large datasets)

When `ext: 'scroll'` is set alongside an `endpoint` or `connector`, the hydrate switches to `input-select-scroll` with `componentType: 'v-combobox'`.

```php
[
    'type'      => 'combobox',
    'name'      => 'city',
    'label'     => 'City',
    'ext'       => 'scroll',
    'connector' => 'Location:City|repository:list',
]
```

## Schema Defaults

| Key | Default | Description |
|-----|---------|-------------|
| `itemValue` | `'id'` | The field used as the option value |
| `itemTitle` | `'name'` | The field displayed for each option |
| `default` | `[]` | Default selection (reset to `null` for single-select) |
| `cascadeKey` | `'items'` | Key used when cascading items between inputs |
| `returnObject` | `false` | Return the full object instead of just the value |

> **Combobox vs Autocomplete:** Both use the same hydrate logic. The difference is the underlying Vuetify component — `v-combobox` allows free-form input while `v-autocomplete` restricts selection to existing options.

## See Also

- [Autocomplete](/guide/form-inputs/input-autocomplete) — Same schema, restricts to existing options only
- [Hydrates reference](/system-reference/hydrates) — Resolution table and schema contract
- [Relationships](/guide/generics/relationships) — Using `connector` to load remote data
