---
sidebarPos: 9
sidebarTitle: Checklist Group
---
# Checklist Group <Badge type="tip" text="^0.9.2" />

The `v-input-checklist-group` component presents radio button selectable schemas. This is useful on scenarios like multiselectable schemas.

## Usage
It needs a schema attribute like standard-schema pattern. Types must be checklist for now.
``` php
  [
    ...,
    'type' => 'checklist-group', // type name
    'schema' => [ // required, for multiple radio options
        [
            'type' => 'checklist',
            'name' => 'country',
            'label' => 'Select Your Country',
            'selectedLabel' => 'Selected Countries',
            'connector' => '{ModuleName}:{RouteName}|repository:list:scopes=hasPackage:with=packageLanguages',
        ],
        [
            'type' => 'checklist',
            'name' => 'packageRegion',
            'label' => 'Select Your Region',
            'selectedLabel' => 'Selected Regions',
            'connector' => '{ModuleName}:{RouteName}|repository:list:scopes=hasPackage',
        ]
    ],
  ],
```

> [!IMPORTANT]
> This component was introduced in [v0.9.2]

## Hydrate

**Class:** `ChecklistGroupHydrate`
**Config type:** `checklist-group`
**Output type:** `input-checklist-group` → `VInputChecklistGroup`

The hydrate sets `type` to `input-checklist-group` and filters the `schema` array to remove any checklist entries that have no `items` — preventing empty groups from rendering.

### Schema Defaults

| Key | Default | Description |
|-----|---------|-------------|
| `default` | `[]` | Default selected values |

## See Also

- [Module Features Overview](/guide/module-features/overview) — Config types and output types (checklist)
- [Hydrates reference](/system-reference/hydrates) — Resolution table and schema contract
