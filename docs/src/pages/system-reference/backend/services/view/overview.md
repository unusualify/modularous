---
sidebarPos: 7
sidebarTitle: Overview
---

# View Services

**Directory**: `src/Services/View/`

The View namespace provides PHP-side builders for constructing Vue component schemas and navigation structures that are passed to the frontend via Inertia props.

## Classes

| Class | Description | Page |
|-------|-------------|------|
| [UComponent](/system-reference/backend/services/view/u-component) | Fluent builder for a single Vue component schema array | [→](/system-reference/backend/services/view/u-component) |
| [UWidget](/system-reference/backend/services/view/u-widget) | Extends `UComponent`; wires Connector data into dashboard widget schemas | [→](/system-reference/backend/services/view/u-widget) |
| [UWrapper](/system-reference/backend/services/view/u-wrapper) | Static factory for grid-based layout wrappers (`v-row` / `v-col`) | [→](/system-reference/backend/services/view/u-wrapper) |
| [ModularousNavigation](/system-reference/backend/services/view/modularous-navigation) | Builds sidebar navigation arrays with permissions, badges, and active states | [→](/system-reference/backend/services/view/modularous-navigation) |

## Rendering Model

`UComponent::render()` returns a plain PHP array:

```php
[
    'tag'        => 'ue-form',
    'attributes' => [...],
    'slots'      => [...],
    'directives' => [...],
    'elements'   => [...],   // only present when children exist
]
```

This array is serialized to JSON by Inertia and consumed by the corresponding Vue component on the frontend. The `tag` key maps directly to a registered Vue component name.
