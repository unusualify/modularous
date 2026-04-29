---
sidebarPos: 1
sidebarTitle: Overview
sidebarGroupTitle: Activators
---

# Activators

**Directory**: `src/Activators/`  
**Namespace**: `Unusualify\Modularity\Activators`

Activators persist and resolve enable/disable state. Modularous uses two activator layers:

- **Module activator** (`ModularityActivator`) controls whether a module is enabled at all.
- **Route activator** (`ModuleActivator`) controls whether specific route actions inside a module are enabled.

## Classes

| Class | Purpose | Page |
|-------|---------|------|
| `ModularityActivator` | Stores and resolves module-level statuses (`enabled` / `disabled`) via JSON + cache | [ModularityActivator →](./modularity-activator) |
| `ModuleActivator` | Stores and resolves route-level statuses per module (`routes_statuses.json`) | [ModuleActivator →](./module-activator) |

## Activation Model

1. A module is discovered and checked by **module-level** status.
2. If module-level status allows it, routes are checked by **route-level** status.
3. Route enable/disable commands update route statuses without fully disabling the module.

This makes it possible to keep a module active while disabling selected route actions.

## Related

- [Module System](/system-reference/modules) — high-level module lifecycle and route actions
- [Commands · route:enable](/guide/console/module/route-enable)
- [Commands · route:disable](/guide/console/module/route-disable)
