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
| [make:module](./make-module) | `modularous:make:module` | Scaffold a complete module (model, controller, repository, migration, routes, hydrate, Vue input) |
| [make:route](./make-route) | `modularous:make:route` | Add a new route entry to an existing module |

## Models & Data

| Command | Signature | Description |
|---------|-----------|-------------|
| [make:model](./make-model) | `modularous:make:model` | Generate an Eloquent model with optional traits, relations, and migration |
| [make:migration](./make-migration) | `modularous:make:migration` | Generate a module migration file |
| [make:repository](./make-repository) | `modularous:make:repository` | Generate a repository class for a module model |
| [create:model-trait](./create-model-trait) | `modularous:create:model:trait` | Create a reusable model trait |
| [create:repository-trait](./create-repository-trait) | `modularous:create:repository:trait` | Create a reusable repository trait |

## Controllers & Requests

| Command | Signature | Description |
|---------|-----------|-------------|
| [make:controller](./make-controller) | `modularous:make:controller` | Generate a standard CRUD controller |
| [make:controller-api](./make-controller-api) | `modularous:make:controller:api` | Generate an API controller |
| [make:controller-front](./make-controller-front) | `modularous:make:controller:front` | Generate a frontend (Inertia) controller |
| [make:request](./make-request) | `modularous:make:request` | Generate a Form Request class |

## Events & Listeners

| Command | Signature | Description |
|---------|-----------|-------------|
| [make:event](./make-event) | `modularous:make:event` | Generate a module event class |
| [make:listener](./make-listener) | `modularous:make:listener` | Generate a module listener class |
| [make:operation](./make-operation) | `modularous:make:operation` | Generate an Operation class for the operations pipeline |

## Frontend

| Command | Signature | Description |
|---------|-----------|-------------|
| [create:input-hydrate](./create-input-hydrate) | `modularous:create:input:hydrate` | Generate a Hydrate class for a module |
| [create:vue-input](./create-vue-input) | `modularous:create:vue:input` | Generate a Vue input component stub |

## Themes & UI

| Command | Signature | Description |
|---------|-----------|-------------|
| [make:theme](./make-theme) | `modularous:make:theme` | Generalise a theme |
| [create:theme](./create-theme) | `modularous:make:theme:folder` | Create a custom theme folder (alias: `modularous:create:theme`) |
| [create:feature](./create-feature) | `modularous:create:feature` | Create a frontend feature module |

## Auth & Users

| Command | Signature | Description |
|---------|-----------|-------------|
| [create:superadmin](./create-superadmin) | `modularous:create:superadmin` | Create the initial superadmin account |
| [create:route-permissions](./create-route-permissions) | `modularous:create:route:permissions` | Generate Spatie permission records for module routes |
| [make:horizon-supervisor](./make-horizon-supervisor) | `modularous:make:horizon:supervisor` | Generate a Horizon supervisor config |

## Tests & Stubs

| Command | Signature | Description |
|---------|-----------|-------------|
| [create:test-laravel](./create-test-laravel) | `modularous:create:test:laravel` | Generate a PHPUnit test stub |
| [create:vue-test](./create-vue-test) | `modularous:create:vue:test` | Generate a Vitest test stub |
| [make:stubs](./make-stubs) | `modularous:make:stubs` | Publish/overwrite generator stub files |
| [make:command](./create-command) | `modularous:make:command` | Generate a new Artisan command class |

> Documentation generation commands live in their own category: see [Docs](../docs/overview).
