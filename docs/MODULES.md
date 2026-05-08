# Module System

## Module vs Route Activation

Modularity has two activation concepts:

1. **Module enable/disable**: Via Nwidart's activator (e.g. `modules_statuses.json` or database). Controls whether a module is loaded at all.

2. **Route enable/disable**: Via `ModuleActivator` and per-module `routes_statuses.json`. Controls which routes within an enabled module are registered.

A module can be enabled but have specific routes disabled (e.g. hide the create route).

## Module Discovery

Modules are scanned from:
- `config('modules.paths.modules')` (default: `modules/`)
- `config('modules.scan.paths')` when scan is enabled

Each module directory must contain `module.json`.

## Module Provider Registration

**Convention**: `ModuleServiceProvider` loads `*ServiceProvider.php` from each module's `Providers/` folder. No need to list providers in `module.json`.

**Optional**: The `providers` array in `module.json` can list additional provider classes for explicit registration. These are merged with the convention-based discovery.

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

## Route Status

Use `php artisan modularity:route:enable` and `modularity:route:disable` to toggle routes. Status is stored in `modules/{ModuleName}/routes_statuses.json`.
