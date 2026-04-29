---
sidebarPos: 31
sidebarTitle: Select Scroll
---

# Select Scroll <Badge type="tip" text="^0.9.1" />

The `select-scroll` input type renders `VInputSelectScroll`, a virtualised select that loads options on demand as the user scrolls. It is designed for large datasets where loading all items upfront is not practical.

The default underlying Vuetify component is **`v-autocomplete`**. This can be changed to `v-combobox` or `v-select` via `componentType`.

> [!NOTE]
> `autocomplete`, `combobox`, and `select` config types also resolve to `input-select-scroll` when `ext: 'scroll'` is set alongside an `endpoint` or `connector`.

## Hydrate

**Class:** `SelectScrollHydrate`
**Config type:** `select-scroll`
**Output type:** `input-select-scroll` → `VInputSelectScroll`

The hydrate:
- Requires an `endpoint` (or `connector` resolved to one) — throws an exception if neither is present and no static `items` are provided
- Sets `componentType` to `v-autocomplete` if not already specified
- Sets `default` to `[]` for multiple-select, `null` for single-select
- Prepends a "Please Select" entry (value `0`) to the loaded items list
- Handles `cascades`: renames relationship keys in the items array to `items` so the cascade mechanism can splice in filtered results

## Usage

### Direct `select-scroll` type

```php
[
    'type'      => 'select-scroll',
    'name'      => 'city_id',
    'label'     => 'City',
    'connector' => 'Location:City|repository:list',
]
```

### Via `ext: 'scroll'` on another select type

```php
[
    'type'      => 'autocomplete', // or 'select', 'combobox'
    'ext'       => 'scroll',
    'name'      => 'country_id',
    'label'     => 'Country',
    'connector' => 'Location:Country|repository:list',
]
```

### Custom component type

```php
[
    'type'          => 'select-scroll',
    'name'          => 'tag_ids',
    'label'         => 'Tags',
    'connector'     => 'Blog:Tag|repository:list',
    'componentType' => 'v-combobox',
    'multiple'      => true,
]
```

## Schema Defaults

| Key | Default | Description |
|-----|---------|-------------|
| `itemValue` | `'id'` | Field used as the option value |
| `itemTitle` | `'name'` | Field displayed for each option |
| `cascadeKey` | `'items'` | Key used when cascading items between inputs |
| `returnObject` | `false` | Return full object instead of just the value |
| `itemsPerPage` | `10` | Number of items fetched per scroll page |
| `multiple` | `false` | Allow multiple selections |
| `componentType` | `'v-autocomplete'` | Underlying Vuetify component |
| `default` | `[]` / `null` | `[]` when `multiple`, `null` otherwise |

> [!IMPORTANT]
> This component was introduced in [v0.9.1]

## See Also

- [Autocomplete](/guide/form-inputs/input-autocomplete) — Uses scroll mode via `ext: 'scroll'`
- [Combobox](/guide/form-inputs/input-combobox) — Uses scroll mode via `ext: 'scroll'`
- [Hydrates reference](/system-reference/hydrates) — Resolution table and schema contract
- [Relationships](/guide/generics/relationships) — Using `connector` to load remote data
