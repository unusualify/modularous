---
sidebarPos: 8
sidebarTitle: Modules
---

# Module System

## Module vs Route Activation

Modularous has two activation concepts:

1. **Module enable/disable**: Via Nwidart's activator (e.g. `modules_statuses.json` or database). Controls whether a module is loaded at all.

2. **Route enable/disable**: Via `ModuleActivator` and per-module `routes_statuses.json`. Controls which routes within an enabled module are registered.

A module can be enabled but have specific routes disabled (e.g. hide the create route).

See [Backend · Activators](/system-reference/backend/activators/overview) for class-level details of `ModularousActivator` and `ModuleActivator`.

## Module Discovery

Modules are scanned from:
- `config('modules.paths.modules')` (default: `modules/`)
- `config('modules.scan.paths')` when scan is enabled

Each module directory must contain `module.json`.

## Module Provider Registration

**Convention**: `ModuleServiceProvider` loads `*ServiceProvider.php` from each module's `Providers/` folder. No need to list providers in `module.json`.

**Optional**: The `providers` array in `module.json` can list additional provider classes for explicit registration.

## Module Structure

```
modules/MyModule/
├── module.json
├── Config/
├── Database/Migrations/
├── Entities/
├── Http/Controllers/
├── Providers/          # *ServiceProvider.php auto-loaded
├── Repositories/
├── Routes/
│   ├── web.php
│   ├── front.php
│   ├── api.php
└── Resources/
    ├── lang/
    └── views/
```

## Route Actions

Standard route actions (Module::$routeActionLists): restore, forceDelete, duplicate, index, create, store, show, edit, update, destroy, bulkDelete, bulkForceDelete, bulkRestore, tags, tagsUpdate, assignments, createAssignment

## Route Status

Use `php artisan modularous:route:enable` and `modularous:route:disable` to toggle routes. Status is stored in `modules/{ModuleName}/routes_statuses.json`.

See [route:enable](/guide/console/module/route-enable) and [route:disable](/guide/console/module/route-disable).
