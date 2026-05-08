---
sidebarPos: 21
sidebarTitle: Json
---

# Json

The `json` input type groups a nested set of inputs under a single field that is serialised as a JSON object. Despite the config type being `json`, the hydrate outputs the schema type `group` — the frontend treats it as a field group rather than a raw JSON editor.

> [!IMPORTANT]
> The output schema type is **`group`**, not `json`. If you are looking for a raw JSON / code-editor component, this is not it.

## Hydrate

**Class:** `JsonHydrate`
**Config type:** `json`
**Output type:** `group`

The hydrate sets `col.cols` to `12` so the group always spans the full row width.

## Usage

```php
[
    'type'   => 'json',
    'name'   => 'meta',
    'label'  => 'Meta',
    'schema' => [
        [
            'type'  => 'text',
            'name'  => 'og_title',
            'label' => 'OG Title',
        ],
        [
            'type'  => 'text',
            'name'  => 'og_description',
            'label' => 'OG Description',
        ],
    ],
]
```

The submitted value for `meta` will be a JSON-encoded object of the nested field values.

## Schema Defaults

| Key | Default | Description |
|-----|---------|-------------|
| `col.cols` | `12` | Always full-width |

## See Also

- [Json Repeater](/guide/form-inputs/input-json-repeater) — Repeatable JSON rows
- [Hydrates reference](/system-reference/hydrates) — Resolution table and schema contract
