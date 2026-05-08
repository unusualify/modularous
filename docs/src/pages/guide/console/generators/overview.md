---
sidebarPos: 10
sidebarTitle: Overview
sidebarGroupTitle: Generators
---

# Generator Commands

Scaffold every layer of a Modularous module — from the module skeleton down to individual controllers, models, migrations, events, Vue inputs, and tests.

## Module Scaffold

| Command | Signature | Description |
|---------|-----------|-------------|
| [make:module](./make-module) | `modularity:make:module` | Scaffold a complete module (model, controller, repository, migration, routes, hydrate, Vue input) |
| [make:route](./make-route) | `modularity:make:route` | Add a new route entry to an existing module |

## Models & Data

| Command | Signature | Description |
|---------|-----------|-------------|
| [make:model](./make-model) | `modularity:make:model` | Generate an Eloquent model with optional traits, relations, and migration |
| [make:migration](./make-migration) | `modularity:make:migration` | Generate a module migration file |
| [make:repository](./make-repository) | `modularity:make:repository` | Generate a repository class for a module model |
| [create:model-trait](./create-model-trait) | `modularity:create:model:trait` | Create a reusable model trait |
| [create:repository-trait](./create-repository-trait) | `modularity:create:repository:trait` | Create a reusable repository trait |

## Controllers & Requests

| Command | Signature | Description |
|---------|-----------|-------------|
| [make:controller](./make-controller) | `modularity:make:controller` | Generate a standard CRUD controller |
| [make:controller-api](./make-controller-api) | `modularity:make:controller:api` | Generate an API controller |
| [make:controller-front](./make-controller-front) | `modularity:make:controller:front` | Generate a frontend (Inertia) controller |
| [make:request](./make-request) | `modularity:make:request` | Generate a Form Request class |

## Events & Listeners

| Command | Signature | Description |
|---------|-----------|-------------|
| [make:event](./make-event) | `modularity:make:event` | Generate a module event class |
| [make:listener](./make-listener) | `modularity:make:listener` | Generate a module listener class |
| [make:operation](./make-operation) | `modularity:make:operation` | Generate an Operation class for the operations pipeline |

## Frontend

| Command | Signature | Description |
|---------|-----------|-------------|
| [create:input-hydrate](./create-input-hydrate) | `modularity:create:input:hydrate` | Generate a Hydrate class for a module |
| [create:vue-input](./create-vue-input) | `modularity:create:vue:input` | Generate a Vue input component stub |

## Themes & UI

| Command | Signature | Description |
|---------|-----------|-------------|
| [make:theme](./make-theme) | `modularity:make:theme` | Generalise a theme |
| [create:theme](./create-theme) | `modularity:make:theme:folder` | Create a custom theme folder (alias: `modularity:create:theme`) |
| [create:feature](./create-feature) | `modularity:create:feature` | Create a frontend feature module |

## Auth & Users

| Command | Signature | Description |
|---------|-----------|-------------|
| [create:superadmin](./create-superadmin) | `modularity:create:superadmin` | Create the initial superadmin account |
| [create:route-permissions](./create-route-permissions) | `modularity:create:route:permissions` | Generate Spatie permission records for module routes |
| [make:horizon-supervisor](./make-horizon-supervisor) | `modularity:make:horizon:supervisor` | Generate a Horizon supervisor config |

## Tests & Stubs

| Command | Signature | Description |
|---------|-----------|-------------|
| [create:test-laravel](./create-test-laravel) | `modularity:create:test:laravel` | Generate a PHPUnit test stub |
| [create:vue-test](./create-vue-test) | `modularity:create:vue:test` | Generate a Vitest test stub |
| [make:stubs](./make-stubs) | `modularity:make:stubs` | Publish/overwrite generator stub files |
| [make:command](./create-command) | `modularity:make:command` | Generate a new Artisan command class |

> Documentation generation commands live in their own category: see [Docs](../docs/overview).
