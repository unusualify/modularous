---
sidebarPos: 33
sidebarTitle: Spread
---

# Spread

The `spread` input type renders `VInputSpread`, a key-value metadata editor. Each row has a **key** field, a **value** field, and a type toggle (string / number / boolean). Users can add, edit, and delete rows; clicking "Save All" commits the changes. Reserved keys (from the model or explicitly configured) are hidden from the editor.

## Hydrate

**Class:** `SpreadHydrate`
**Config type:** `spread`
**Output type:** `input-spread` → `VInputSpread`

The hydrate:
- Sets `type` to `input-spread` and forces `col` to full-width (`cols: 12` across all breakpoints)
- When `_moduleName` / `_routeName` are present:
  - Loads `reservedKeys` from `$model->getReservedKeys()` and merges any `spreadable` inputs from the route's schema
  - Sets `name` to `$model->getSpreadableSavingKey()`
- Without module context: falls back to `reservedKeys: []` and `name: 'spread_payload'`
- Accepts an inline `scrollable` flag that toggles vertical scrolling on the rows container

## Usage

### Auto-resolved (module context)

```php
[
    'type' => 'spread',
]
```

### Explicit with reserved keys

```php
[
    'type'         => 'spread',
    'name'         => 'meta',
    'reservedKeys' => ['id', 'created_at', 'updated_at'],
]
```

### Scrollable with fixed height

```php
[
    'type'       => 'spread',
    'scrollable' => true,
    'height'     => '400px',
]
```

## Schema Defaults

| Key | Default | Description |
|-----|---------|-------------|
| `col` | `{cols:12, sm:12, md:12, lg:12, xl:12}` | Always full width |
| `name` | `'spread_payload'` | Field name (or model's spreadable key) |
| `reservedKeys` | `[]` | Keys hidden/blocked from editing |
| `scrollable` | `false` | Enable vertical scroll on the row list |
| `height` | `'300px'` | Height of the scrollable rows container |

## See Also

- [Json](/guide/form-inputs/input-json) — Grouped JSON fields (fixed schema)
- [Repeater](/guide/form-inputs/input-repeater) — Repeatable rows with a defined schema
- [Hydrates reference](/system-reference/hydrates) — Resolution table and schema contract
