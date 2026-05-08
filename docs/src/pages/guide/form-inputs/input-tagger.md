---
sidebarPos: 36
sidebarTitle: Tagger
---

# Tagger

The `tagger` input type renders `VInputTagger`, a free-form tag creator and selector built on Vuetify's `v-combobox`. Users can select existing tags (displayed as coloured chips) or type a new value to create one on the fly. Existing tags are editable inline. Unlike [Tag](/guide/form-inputs/input-tag), Tagger manages its own tag entity CRUD (create / rename) rather than just selecting from a pre-existing namespace.

## Hydrate

**Class:** `TaggerHydrate`
**Config type:** `tagger`
**Output type:** `input-tagger` → `VInputTagger`

The hydrate:
- Requires `_moduleName` and `_routeName`; throws if the repository does not use `TagsTrait`
- Sets `fetchEndpoint` and `updateEndpoint` from the module route action URLs
- Loads `items` via `repository->getTags()`, prepending a "Select an option or create one" header
- Each item is enriched with a `color` cycled from the `colors` array
- For translated tags, items are grouped by locale with a header in each group

## Usage

### Standard (auto-resolved)

```php
[
    'type' => 'tagger',
]
```

### Translated tags

```php
[
    'type'       => 'tagger',
    'translated' => true,
]
```

### Custom colors

```php
[
    'type'   => 'tagger',
    'colors' => ['red', 'blue', 'green', 'yellow'],
]
```

## Schema Defaults

| Key | Default | Description |
|-----|---------|-------------|
| `itemValue` | `'id'` | Field used as the tag value |
| `itemTitle` | `'name'` | Field displayed for each tag |
| `default` | `[]` | No tags selected by default |
| `returnObject` | `false` | Return tag IDs not full objects |
| `label` | `'Tags'` | Field label |
| `name` | `'tags'` | Form field name |
| `multiple` | `true` | Allow multiple tag selections |
| `colors` | `['green','purple','indigo','cyan','teal','orange']` | Chip colour cycle |

> **Tagger vs Tag:** Use `tagger` when users should be able to **create new tags** from within the form. Use `tag` when tags are managed centrally and users only select from an existing set.

## See Also

- [Tag](/guide/form-inputs/input-tag) — Read-only tag selector from a pre-existing namespace
- [Hydrates reference](/system-reference/hydrates) — Resolution table and schema contract
