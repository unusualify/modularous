---
sidebarPos: 0
sidebarTitle: Components Overview
---

# Components Overview

Modularity's Vue components are organized by purpose. Most are in `vue/src/js/components/`.

## Organization

| Location | Purpose |
|----------|---------|
| `components/` | Root components (Form, Auth, Table, etc.) |
| `components/layouts/` | Layout components (Main, Sidebar, Home) |
| `components/inputs/` | Form input components |
| `components/modals/` | Modal components |
| `components/table/` | Table-related components |
| `components/data_iterators/` | RichRowIterator, RichCardIterator |
| `components/customs/` | App-specific overrides (UeCustom*) |
| `components/labs/` | **Experimental** — not guaranteed stable |

## Labs Components

Components in `labs/` are experimental. They may change or be removed. Use with caution.

Current labs: InputDate, InputColor, InputTreeview, etc.

To enable labs in build, set `VUE_ENABLE_LABS=true` (if supported by your build config).

## Input Registry

Custom input types are registered via `@/components/inputs/registry`:

```js
import { registerInputType } from '@/components/inputs/registry'
registerInputType('my-input', 'VMyInput')
```

See [Hydrates](/system-reference/hydrates) for the backend schema flow.

## Composition API

New components should use Vue 3 Composition API. Existing Options API components are being migrated incrementally.
