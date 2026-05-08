---
sidebarPos: 3
sidebarTitle: Autocomplete
---

# Autocomplete

The `autocomplete` input type renders a searchable select field backed by Vuetify's `v-autocomplete`. It supports both static item lists and remote data via `connector` or `endpoint`.

## Hydrate

**Class:** `AutocompleteHydrate`
**Config type:** `autocomplete`
**Output type:** `select` (with autocomplete behaviour) or `input-select-scroll` when scroll mode is active

## Usage

### Static items

```php
[
    'type'       => 'autocomplete',
    'name'       => 'category_id',
    'label'      => 'Category',
    'items'      => $categories, // pre-loaded array
    'itemValue'  => 'id',
    'itemTitle'  => 'name',
]
```

### Remote items via connector

```php
[
    'type'      => 'autocomplete',
    'name'      => 'category_id',
    'label'     => 'Category',
    'connector' => 'Blog:Category|repository:list',
]
```

### Scroll mode (large datasets)

When `ext: 'scroll'` is set alongside an `endpoint` or `connector`, the hydrate automatically switches the output type to `input-select-scroll` with `componentType: 'v-autocomplete'`.

```php
[
    'type'      => 'autocomplete',
    'name'      => 'tag_id',
    'label'     => 'Tag',
    'ext'       => 'scroll',
    'connector' => 'Blog:Tag|repository:list',
]
```

### Multiple selection

```php
[
    'type'     => 'autocomplete',
    'name'     => 'tag_ids',
    'label'    => 'Tags',
    'multiple' => true,
    'connector' => 'Blog:Tag|repository:list',
]
```

## Schema Defaults

| Key | Default | Description |
|-----|---------|-------------|
| `itemValue` | `'id'` | The field used as the option value |
| `itemTitle` | `'name'` | The field used as the option label |
| `default` | `[]` | Default selection (reset to `null` for single-select) |
| `cascadeKey` | `'items'` | Key used when cascading items between inputs |
| `returnObject` | `false` | Return the full object instead of just the value |

## See Also

- [Hydrates reference](/system-reference/hydrates) — Resolution table and schema contract
- [Relationships](/guide/generics/relationships) — Using `connector` to load remote data
