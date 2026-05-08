---
sidebarPos: 1
sidebarTitle: Overview
sidebarGroupTitle: Make
---

# Make Commands

The `make:*` group scaffolds every layer of a Modularous application — from a full module skeleton to individual traits, Vue components, and test files. Every command lives under `src/Console/Make/` and extends `BaseCommand`.

## Module & Route Scaffold

| Command | Signature | Description |
|---------|-----------|-------------|
| [make:module](./module) | `modularity:make:module` | Bootstrap a complete module (nWidart skeleton + first route) |
| [make:route](./route) | `modularity:make:route` | Add a new route to an existing module |
| [make:stubs](./stubs) | `modularity:make:stubs` | Selectively regenerate stub files for an existing route |

## Models & Data

| Command | Signature | Description |
|---------|-----------|-------------|
| [make:model](./model) | `modularity:make:model` | Eloquent model with optional traits, relations, and companion models |
| [make:migration](./migration) | `modularity:make:migration` | Database migration (create, pivot, morph-pivot, add, drop) |
| [make:repository](./repository) | `modularity:make:repository` | Repository class bound to a module model |
| [make:model:trait](./model-trait) | `modularity:make:model:trait` | Reusable entity trait (`Has{Name}.php`) |
| [make:repository:trait](./repository-trait) | `modularity:make:repository:trait` | Reusable repository trait (`{Name}Trait.php`) |

## Controllers & Requests

| Command | Signature | Description |
|---------|-----------|-------------|
| [make:controller](./controller) | `modularity:make:controller` | Admin-panel CRUD controller |
| [make:controller:api](./controller-api) | `modularity:make:controller:api` | REST API controller |
| [make:controller:front](./controller-front) | `modularity:make:controller:front` | Front-end (public-facing) controller |
| [make:request](./request) | `modularity:make:request` | Form Request with inline validation rules |
| [make:route:permissions](./route-permissions) | `modularity:make:route:permissions` | Generate Spatie permission records for a route |

## Events & Async

| Command | Signature | Description |
|---------|-----------|-------------|
| [make:event](./event) | `modularity:make:event` | Laravel Event class (broadcasting, deferred dispatch) |
| [make:listener](./listener) | `modularity:make:listener` | Laravel Listener class (queued, after-commit) |
| [make:operation](./operation) | `modularity:make:operation` | One-time operation file for the operations pipeline |
| [make:horizon:supervisor](./horizon-supervisor) | `modularity:make:horizon:supervisor` | Supervisor `.conf` for Laravel Horizon |

## Console

| Command | Signature | Description |
|---------|-----------|-------------|
| [make:command](./command) | `modularity:make:command` | New Artisan command class inside the Modularous vendor path |

## Themes & Frontend

| Command | Signature | Description |
|---------|-----------|-------------|
| [make:theme:folder](./theme-folder) | `modularity:make:theme:folder` | Scaffold a custom theme working folder |
| [make:theme](./theme) | `modularity:make:theme` | Promote a custom theme into the built-in theme set |
| [make:input:hydrate](./input-hydrate) | `modularity:make:input:hydrate` | PHP Hydrate class for a Vue input component |
| [make:vue:input](./vue-input) | `modularity:make:vue:input` | Vue single-file input component |

## Tests

| Command | Signature | Description |
|---------|-----------|-------------|
| [make:laravel:test](./laravel-test) | `modularity:make:laravel:test` | PHPUnit Feature or Unit test file |
| [make:vue:test](./vue-test) | `modularity:make:vue:test` | Vitest/Jest test file for a Vue component or composable |

## Composite Wizard

| Command | Signature | Description |
|---------|-----------|-------------|
| [make:feature](./feature) | `modularity:make:feature` | Interactive wizard that orchestrates multiple make commands |

---

## Common Workflows

### Scaffold a brand-new module

```bash
php artisan modularity:make:module Blog --schema="title:string,body:text" --addTranslation
```

### Add a second route to an existing module

```bash
php artisan modularity:make:route Blog Post --schema="title:string,published_at:timestamp:nullable"
```

### Create a standalone model + migration

```bash
php artisan modularity:make:model Tag Blog --soft-delete
php artisan modularity:make:migration create_blog_tags_table Blog --fields="tag_id:unsignedBigInteger"
```

### Scaffold a Vue input feature end-to-end

```bash
php artisan modularity:make:feature
# Responds to all prompts interactively
```

> For the class internals of these commands see [System Reference → Console → Make](/system-reference/backend/console/make).
