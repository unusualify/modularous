---
sidebarPos: 30
sidebarTitle: Repeater
---

# Repeater

The `repeater` input type renders `VInputRepeater`, a dynamic form block that lets users add, remove, duplicate, and drag-to-reorder rows. Each row renders its own sub-form from the `schema` array. It is used for one-to-many relational data or repeatable JSON objects.

## Hydrate

**Class:** `RepeaterHydrate`
**Config type:** `repeater`
**Output type:** `input-repeater` → `VInputRepeater`

The hydrate:
- Sets `root` to `'default'` for `repeater` type, or to the original type name for subtypes (e.g. `json-repeater` sets `root: 'json-repeater'`)
- Sets `singularLabel` from the singular of `label`
- Sets `col.cols: 12` for full-width layout
- When `draggable: true`, defaults `orderKey` to `'position'`
- Auto-resolves foreign key fields in `schema` when `repository`, `model`, or `newConnector` is provided

## Usage

### Basic repeater

```php
[
    'type'          => 'repeater',
    'name'          => 'contacts',
    'label'         => 'Contacts',
    'singularLabel' => 'Contact',
    'schema'        => [
        [
            'type'  => 'text',
            'name'  => 'name',
            'label' => 'Name',
        ],
        [
            'type'  => 'text',
            'name'  => 'email',
            'label' => 'Email',
        ],
    ],
]
```

### Draggable with order key

```php
[
    'type'     => 'repeater',
    'name'     => 'items',
    'label'    => 'Items',
    'draggable'=> true,
    'orderKey' => 'sort_order',
    'schema'   => [
        ['type' => 'text', 'name' => 'title', 'label' => 'Title'],
    ],
]
```

### Relational repeater with repository

Provide `repository` to let the hydrate auto-resolve foreign key fields inside `schema`:

```php
[
    'type'       => 'repeater',
    'name'       => 'product_features',
    'label'      => 'Features',
    'repository' => \App\Repositories\FeatureRepository::class,
    'schema'     => [
        [
            'type'  => 'select',
            'name'  => 'feature_id',
            'label' => 'Feature',
            // items auto-resolved from repository
        ],
        [
            'type'  => 'text',
            'name'  => 'value',
            'label' => 'Value',
        ],
    ],
]
```

## Schema Defaults

| Key | Default | Description |
|-----|---------|-------------|
| `autoIdGenerator` | `true` | Auto-assign IDs to new rows |
| `itemValue` | `'id'` | Field used as the row identifier |
| `itemTitle` | `'name'` | Field used as the row display title |
| `root` | `'default'` | Storage root key (`'json-repeater'` for JSON variant) |
| `col.cols` | `12` | Always full width |
| `orderKey` | `'position'` | Sort key (only when `draggable: true`) |

## See Also

- [Json Repeater](/guide/form-inputs/input-json-repeater) — Repeater that serialises rows as JSON objects
- [Hydrates reference](/system-reference/hydrates) — Resolution table and schema contract
