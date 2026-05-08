---
sidebarPos: 5
sidebarTitle: Input Treeview
---

# InputTreeview <Badge type="warning" text="experimental" />

`InputTreeview` wraps Vuetify's `v-treeview` inside the `ue-form` schema system, enabling hierarchical item selection as a form field.

## Schema usage

```php
[
  'type'  => 'treeview',
  'name'  => 'category_ids',
  'label' => 'Categories',
  'items' => $categories,   // nested array — see structure below
  'item-title' => 'name',
  'item-value' => 'id',
  'selectable' => true,
  'open'  => [],            // array of initially open node IDs
]
```

## Items structure

The `items` array must be a nested tree where each node can have a `children` key:

```php
[
  [
    'id'       => 1,
    'name'     => 'Technology',
    'children' => [
      ['id' => 2, 'name' => 'Software'],
      ['id' => 3, 'name' => 'Hardware'],
    ],
  ],
  [
    'id'   => 4,
    'name' => 'Science',
  ],
]
```

## Value format

The model value is an array of selected item values (e.g. IDs). In `selectable` mode, Vuetify handles intermediate states for parent nodes automatically.

## Schema keys

All keys on the schema object are forwarded to `v-treeview` via `v-bind="obj.schema"`. Commonly used keys:

| Key | Description |
|---|---|
| `items` | The tree data array |
| `item-title` | Property used as the node label (default `title`) |
| `item-value` | Property used as the node value (default `value`) |
| `selectable` | Enables checkboxes for multi-selection |
| `open` | Array of initially expanded node IDs |
| `open-all` | Opens all nodes by default |
| `select-strategy` | `'leaf'`, `'independent'`, or `'classic'` selection behaviour |

## Notes

- Both `v-model` (selected values) and `v-model:active` are bound to the same `input` ref, which may not suit all use cases — use `select-strategy` to control the selection behaviour precisely.
- `v-model:open` is bound to `obj.schema.open`, so the open state is driven by the schema rather than a separate data property.
