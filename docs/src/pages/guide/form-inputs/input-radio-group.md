---
sidebarPos: 28
sidebarTitle: Radio Group
---

# Radio Group <Badge type="tip" text="^0.9.2" />

The `radio-group` input type renders `VInputRadioGroup`, a set of radio buttons where exactly one option can be selected. Items can be provided inline or loaded via a `connector`.

## Hydrate

**Class:** `RadioGroupHydrate`
**Config type:** `radio-group`
**Output type:** `input-radio-group` → `VInputRadioGroup`

The hydrate sets the `default` value to the `itemValue` of the first item in the `items` array. If `items` is empty or not provided, `default` is left unset.

## Usage

```php
[
    'type'      => 'radio-group',
    'name'      => 'plan',
    'label'     => 'Plan',
    'itemValue' => 'id',
    'itemTitle' => 'name',
    'items'     => [
        ['id' => 1, 'name' => 'Basic'],
        ['id' => 2, 'name' => 'Pro'],
        ['id' => 3, 'name' => 'Enterprise'],
    ],
]
```

## Schema Defaults

| Key | Default | Description |
|-----|---------|-------------|
| `default` | first item's `itemValue` | Pre-selected option; auto-set from `items[0]` |
| `itemValue` | `'id'` | The field used as the option value |
| `itemTitle` | `'name'` | The field displayed for each option |

## See Also

- [Hydrates reference](/system-reference/hydrates) — Resolution table and schema contract

> [!IMPORTANT]
> This component was introduced in [v0.9.2]
