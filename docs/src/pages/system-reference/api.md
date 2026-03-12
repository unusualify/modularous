---
sidebarPos: 9
sidebarTitle: API
---

# API Guide

Common use cases and patterns for developers.

## Adding a New Module

1. Create `modules/MyModule/` with `module.json`
2. Add `Config/`, `Database/Migrations/`, `Entities/`, `Http/Controllers/`, `Repositories/`, `Routes/`
3. Enable via `modules_statuses.json` or `php artisan module:enable MyModule`
4. Run `php artisan modularity:build` to rebuild Vue assets if the module adds frontend pages

## Adding a New Input Type

1. Create component in `vue/src/js/components/inputs/` (e.g. `InputPrice.vue`)
2. Register in app bootstrap or a plugin:

```js
import { registerInputType } from '@/components/inputs/registry'
registerInputType('input-price', 'VInputPrice')
```

3. Use in schema: `{ myField: { type: 'input-price', ... } }`

See [Hydrates](./hydrates#adding-a-new-input) for full flow (PHP Hydrate + Vue component).

## Repository Pattern

- All data access goes through repositories
- Use `$this->repository` in controllers (from PanelController)
- Lifecycle: `prepareFieldsBeforeCreate` → `create` → `beforeSave` → `prepareFieldsBeforeSave` → `save` → `afterSave`
- See [Repositories](./repositories) for full lifecycle

## Controller Flow

- `preload()` — runs before index/create/edit; calls `addWiths()`, `setupFormSchema()`
- `setupFormSchema()` — hydrates form inputs via InputHydrator
- `index()` — `addWiths()`, `addIndexWiths()`, `respondToIndexAjax()` for AJAX, else `getIndexData()` → `renderIndex()`
- `create()` / `edit()` — load form schema, pass to view/Inertia

## Finder

- `Finder::getModel($table)` — resolve model class from table name (scans modules, then app/Models)
- `Finder::getRouteModel($routeName)` — resolve model from route name
- Used by Module to resolve repository, model, controller

## Route Generation

Use `php artisan modularity:make:route` to scaffold routes, migrations, controllers, repositories from module config. See [make:route](/guide/commands/Generators/make-route).

## Currency Provider

When adding pricing without SystemPricing module:

1. Implement `CurrencyProviderInterface`
2. Register in config: `modularity.currency_provider` = YourProvider::class
3. Or bind in a service provider: `$app->singleton(CurrencyProviderInterface::class, YourProvider::class)`

## Helpers (Frontend)

Prefer imports over window globals:

```js
import { isObject, dataGet, isset } from '@/utils/helpers'
```

## Config Keys

- `modularity.services.*` — service config (currency_exchange, etc.)
- `modularity.roles` — role definitions
- `modularity.traits` — entity traits
- `modularity.paths` — base paths
