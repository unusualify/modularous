---
sidebarPos: 18
sidebarTitle: Form Tabs
---

# Form Tabs <Badge type="tip" text="^0.9.2" />

The `tab-group` input type renders `VInputFormTabs`, which wraps a nested `schema` array into a tabbed form layout. Each tab contains its own set of inputs. The hydrate automatically discovers which nested inputs require eager or lazy relationship loading so the parent form can pre-fetch the right data.

> [!NOTE]
> This is different from the display-only `ue-tab-groups` component. `Form Tabs` is an input that participates in form submission; `ue-tab-groups` is a layout wrapper for displaying content in tabs.

## Hydrate

**Class:** `FormTabsHydrate`
**Config type:** `tab-group`
**Output type:** `input-form-tabs` → `VInputFormTabs`

The hydrate scans each input in the nested `schema` and builds two lists:

- **`eagers`** — relationships that should be loaded immediately (inputs of type `checklist`, `select`, `combobox`, `autocomplete`, `comparison-table` whose connector/repository resolves an endpoint)
- **`lazy`** — relationships deferred until the tab is first opened

Set `noEager: true` on an individual schema input to force it into the `lazy` list regardless of type.

## Usage

```php
[
    'type'   => 'tab-group',
    'name'   => 'details',
    'label'  => 'Details',
    'schema' => [
        [
            'tab'    => 'General',
            'inputs' => [
                [
                    'type'  => 'text',
                    'name'  => 'title',
                    'label' => 'Title',
                ],
                [
                    'type'      => 'select',
                    'name'      => 'category_id',
                    'label'     => 'Category',
                    'connector' => 'Blog:Category|repository:list',
                ],
            ],
        ],
        [
            'tab'    => 'Settings',
            'inputs' => [
                [
                    'type'  => 'checkbox',
                    'name'  => 'is_published',
                    'label' => 'Published',
                ],
            ],
        ],
    ],
]
```

### Deferring a relationship to lazy load

```php
[
    'type'     => 'select',
    'name'     => 'tag_ids',
    'label'    => 'Tags',
    'connector'=> 'Blog:Tag|repository:list',
    'noEager'  => true,   // load only when this tab is first opened
]
```

## Schema Defaults

No top-level defaults are set by the hydrate. All schema is driven by the `schema` array you provide.

| Key | Description |
|-----|-------------|
| `schema` | **Required.** Array of tab objects, each with a `tab` label and `inputs` array |
| `eagers` | Auto-populated: relationships to load immediately |
| `lazy` | Auto-populated: relationships to defer until tab open |

## See Also

- [Hydrates reference](/system-reference/hydrates) — Resolution table and schema contract
- [Relationships](/guide/generics/relationships) — Using `connector` to load remote data
