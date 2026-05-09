---
sidebarPos: 7
sidebarTitle: CoreController
---

# CoreController

**File**: `src/Http/Controllers/CoreController.php`  
**Namespace**: `Unusualify\Modularous\Http\Controllers`  
**Extends**: `Controller`  
**Traits**: `AuthorizesRequests`, `DispatchesJobs`, `ValidatesRequests`, `ManageNames`, `Moduleable`, `ManageModuleRoute`, `ManageTraits`

Foundation for all Modularous panel and API controllers. On construction it discovers the current module from the file path, resolves the repository, and loads module configuration — so subclasses always have `$moduleName`, `$modelName`, `$config`, and `$repository` available.

## Constructor

```php
public function __construct(Application $app, Request $request)
```

1. Stores `$app` and `$request`.
2. Calls trait hooks: `__beforeConstruct()` → `preload()` → `__afterConstruct()`.

### Hook methods

| Method | Purpose |
|--------|---------|
| `__beforeConstruct(...$args)` | Called before `preload()`. Traits may override this to run setup first. |
| `__afterConstruct(...$args)` | Called after `preload()`. Traits may override this to run setup last. |

## Properties

| Property | Type | Description |
|----------|------|-------------|
| `$app` | `Application` | Laravel service container |
| `$baseKey` | `string` | Module namespace key in snake_case |
| `$request` | `Request` | Current HTTP request |
| `$namespace` | `string` | Module PHP namespace |
| `$moduleName` | `string` | Discovered module name |
| `$modelName` | `string` | Model name (capitalised route name) |
| `$config` | `object` | Module configuration object |
| `$repository` | `Repository\|null` | Model repository instance |

## Module Discovery

### `getModuleName(): ?string`

Discovers the module name by matching the controller's file path against the registered modules directories. Returns the first segment after the modules root, e.g. `app/Modules/Blog/...` → `Blog`.

### `getNamespace(): string`

Returns the PHP namespace for the current module (e.g. `Modules\Blog`).

### `preload(): void`

Loads `$moduleName`, `$modelName`, `$config`, `$routeName`, and `$repository`. Called automatically during construction.

## Repository

### `getRepository(): ?Repository`

Initialises and returns the model repository. Resolves by convention — looks for `{Module}\Repositories\{Model}Repository` in the container.

### `getRepositoryClass(string $model): ?string`

Finds the repository class for a given model name. Returns `null` if none is registered.

## Configuration

### `getModuleConfig(): object`

Returns the full configuration object for the current module route.

### `getConfigFieldsByRoute(string $fieldName, $default = null): mixed`

Gets a config field for the current route. Returns `$default` if absent.

### `getConfigFieldsByRouteRaw(string $fieldName, $default = null): mixed`

Same as above but bypasses any transformation — returns raw config values.

## Route Utilities

### `routeParameters(): array`

Returns all parameters from the current route.

### `routeArguments(): array`

Returns route parameters merged with host-routing context (e.g. tenant ID).

### `routeModuleArguments(): array`

Converts route argument keys to StudlyCase — useful when passing arguments to module-aware methods.

### `routeArgument(): mixed`

Returns the argument value for the current route (first parameter).

### `parentRouteArguments(): array`

Returns only arguments that belong to parent routes (excludes current route parameter).

### `routeHasTrait(string $behavior): bool`

Returns `true` if the repository implements the given behaviour trait (e.g. `HasSoftDelete`, `HasRevisions`).

### `routeHas(string $behavior): bool`

Alias for `routeHasTrait`.

## Tag & Assignment Endpoints

These methods are called from generated module routes automatically.

### `tags(): JsonResponse`

Returns all available tags for the current model, used to populate tag input fields.

### `tagsUpdate(): JsonResponse`

Creates a new tag and returns it. Called when a user types a new tag value in the UI.

### `assignments(int $id): JsonResponse`

Returns the current assignments for a model instance.

### `createAssignment(int $id): JsonResponse`

Creates or updates an assignment with status and optional file attachments.
