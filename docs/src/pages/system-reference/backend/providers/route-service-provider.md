---
sidebarPos: 7
sidebarTitle: RouteServiceProvider
---

# RouteServiceProvider

**Class**: `Unusualify\Modularous\Providers\RouteServiceProvider`  
**Source**: `src/Providers/RouteServiceProvider.php`  
**Extends**: `Illuminate\Foundation\Support\Providers\RouteServiceProvider`

Registers all Modularous system routes, iterates enabled modules to register their routes, and defines the route macros used throughout the application.

## `boot()`

1. Registers route macros (`bootMacros()`)
2. Registers route middleware aliases (`bootRouteMiddlewares()` → `ModularousRoutes::generateRouteMiddlewares()`)
3. Loads `routes/channels.php` for broadcasting
4. Calls `parent::boot()` which triggers `map()`

## Route Map

### System routes (`mapSystemRoutes`)

| Group | Middleware | File |
|-------|-----------|------|
| Auth routes | `web` + default middlewares | `routes/auth.php` |
| Admin web routes | Domain-scoped (`admin_app_url`) + web panel middlewares | `routes/web.php` |
| Admin API routes | `/api` prefix + web panel middlewares | `routes/api.php` |
| Front routes | `web` | `routes/front.php` |
| Glide image route | — | `GET /{glide.base_path}/{path}` → `GlideController` |

The Glide route is only registered when `media_library.image_service` is set to the Glide service class.

### Module routes (`mapModuleRoutes`)

For each enabled module, six route groups are registered:

| Group | Scope | Source file |
|-------|-------|-------------|
| Module web (manual) | Module prefix + admin domain | `Routes/web.php` |
| Module front (manual) | `app.url` domain | `Routes/front.php` |
| Module panel (macro) | Admin domain + panel middlewares | `Routes/web.php` via `Route::moduleRoutes()` |
| Module front (macro) | `app.url` + web middlewares | `Routes/front.php` via `Route::moduleFrontRoutes()` |
| Module public API | `ModularousRoutes::getPublicApiGroupOptions()` | `Routes/public-api.php` (if exists) |
| Module auth API | `ModularousRoutes::getAuthApiGroupOptions()` | `Routes/api.php` (if exists) |
| Module API (macro) | `api.` prefix | `Routes/api.php` via `Route::moduleApiRoutes()` |

## Route Macros

| Macro | HTTP | Description |
|-------|------|-------------|
| `Route::hasAdmin($name)` | — | Returns the fully-qualified admin route name if it exists, `false` otherwise |
| `Route::host(...$models)` | — | Delegates to `HostRoutingRegistrar::host()` for host-based route binding |
| `Route::moduleRoutes($module)` | — | Registers admin panel routes for a module via `ModularousRoutes::registerModuleRoutes()` |
| `Route::moduleFrontRoutes($module)` | — | Registers front-facing routes for a module |
| `Route::moduleApiRoutes($module)` | — | Registers API routes for a module |
| `Route::additionalRoutes($url, $name, $options)` | Mixed | Registers a standard set of extra routes: `reorder`, `restore`, `bulkRestore`, `forceDelete`, `bulkForceDelete`, `bulkDelete`, `duplicate`, `tags`, `tagsUpdate`, `assignments`, `createAssignment` |
| `Route::apiAdditionalRoutes($url, $name, $options)` | Mixed | Registers API-specific extra routes from `ModularousRoutes::getCustomApiRoutes()` |

### `additionalRoutes` HTTP verbs

| Route | Verb |
|-------|------|
| `reorder`, `bulkPublish`, `bulkFeature`, `bulkDelete`, `bulkRestore`, `bulkForceDelete` | POST |
| `publish`, `feature`, `restore`, `forceDelete`, `tagsUpdate` | PUT |
| `duplicate`, `preview` | PUT `/{id}` |
| `browser`, `tags` | GET |
| `restoreRevision` | GET `/{id}` |
| `assignments` | GET `/{id}/assignments` |
| `createAssignment` | POST `/{id}/assignments` |
