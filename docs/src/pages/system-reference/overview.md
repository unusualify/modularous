---
sidebarPos: 1
sidebarTitle: System Reference
---

# System Reference

Modularity (Modularous) is a Laravel package that provides a modular admin panel powered by Vue.js, Vuetify, and Inertia. It uses the Repository pattern for data access, config-driven forms and tables, and a Hydrate system to transform module config into frontend schema.

## Documentation Index

| Page | Description |
|------|-------------|
| [Architecture](./architecture) | System overview, request flow, schema flow, core classes |
| [Hydrates](./hydrates) | Backend → frontend schema transformation (input types) |
| [Repositories](./repositories) | Data access layer, lifecycle, Logic traits |
| [Backend](./backend/overview) | Controllers, Console commands, Entities, Services |
| [Frontend](./frontend/overview) | Vue structure, form/table flow, hooks, store |
| [Config](./config) | Configuration layers (merges, defers, publishes) |
| [Modules](./modules) | Module vs route activation, structure |
| [API](./api) | Common patterns and use cases |
| [Pinia Migration](./pinia-migration) | Vuex → Pinia migration path |
| [Console Conventions](./console-conventions) | Command naming and signature rules |
| [Entities](./entities) | Models, entity traits, enums |
| [Features Pattern](./features) | Entity + Repository + Hydrate triple pattern |

## Quick Reference

**Key config keys**
- `modularity.services.*` — services (currency_exchange, etc.)
- `modularity.roles` — role definitions
- `modularity.traits` — entity traits
- `modularity.paths` — base paths
- `modularity.currency_provider` — currency provider FQCN

**Key commands**
- `modularity:build` — rebuild Vue assets
- `modularity:route:enable` / `modularity:route:disable` — toggle routes
- `modularity:route:status` — list route status per module

**Paths**
- Package source: `packages/modularous/src/`
- Vue source: `packages/modularous/vue/src/js/`
- Modules: `config('modules.paths.modules')` (default: `modules/`)

## For Contributors

See [AGENTS.md](https://github.com/unusualify/modularous/blob/main/AGENTS.md) for package development rules, patterns, and conventions.
