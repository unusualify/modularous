---
sidebarPos: 17
sidebarTitle: PanelController
---

# PanelController

**File**: `src/Http/Controllers/PanelController.php`  
**Namespace**: `Unusualify\Modularity\Http\Controllers`  
**Extends**: `CoreController`  
**Implements**: `CacheableInterface`  
**Traits**: `MakesResponses`, `ManageScopes`, `ManageAuthorization`, `CacheableResponse`, `ManageWiths`, `ManageAppends`

Abstract controller for authenticated admin-panel routes. Adds permission-based middleware, scope handling, nested route detection, and paginated index fetching on top of `CoreController`.

All generated module admin controllers extend `BaseController` which extends this class.

## Constructor

```php
public function __construct(Application $app, Request $request)
```

1. Applies `auth.modularity` and `verified` middleware.
2. Calls `preload()` (which triggers `CoreController::preload()` and adds nested/scope setup).
3. Calls `setMiddlewarePermission()` if `$setDefaultPermissions` is `true`.

## Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$user` | `Model` | — | Authenticated user |
| `$routePrefix` | `string` | — | Route prefix for URL generation |
| `$isParent` | `bool` | — | Whether current route is a parent |
| `$isNested` | `bool` | `false` | Whether current route is nested |
| `$nestedParentId` | `int` | — | Parent record ID when nested |
| `$nestedParentName` | `string` | — | Parent route name when nested |
| `$nestedParentModel` | `Model` | — | Parent model instance when nested |
| `$modelTitle` | `string` | — | Human-readable model title |
| `$defaultIndexOptions` | `array` | See below | Default CRUD visibility flags |
| `$indexOptions` | `array` | `[]` | Overridable CRUD visibility flags |
| `$perPage` | `int` | `10` | Items per page |
| `$titleColumnKey` | `string` | `'name'` | Column used as model title |
| `$setDefaultPermissions` | `bool` | `true` | Register permission middleware automatically |

### Default index options

```php
[
    'create'   => true,
    'edit'     => true,
    'delete'   => true,
    'restore'  => false,
    'forceDelete' => false,
    'duplicate' => false,
    'reorder'  => false,
    'publish'  => false,
]
```

Override per-controller by setting `$indexOptions`.

## Permissions

### `setMiddlewarePermission(): void`

Registers middleware for each CRUD action:

| Action | Permission checked |
|--------|--------------------|
| `index`, `create`, `store` | `{routeName}.view` |
| `edit`, `update` | `{routeName}.edit` |
| `destroy`, `forceDelete`, `bulkDelete` | `{routeName}.delete` |
| `restore`, `bulkRestore` | `{routeName}.restore` |

### `permissionPrefix(string $permission): string`

Generates a dot-separated permission name, e.g. `blog.posts.edit`.

### `isGateable(): bool`

Returns `true` when permission gates are enabled for the current module.

## Nested Routes

### `checkNestedAttributes(): void`

Detects whether the current route is nested and populates `$isNested`, `$nestedParentId`, `$nestedParentName`, `$nestedParentModel`.

### `getNestedAttributes(): array`

Returns `[isNested, parentId, parentName, parentModel]`.

### `getParentModuleForeignKey(): string`

Returns the foreign key column name pointing to the parent (e.g. `blog_id`).

### `nestedParentScopes(): array`

Returns query scopes that limit results to the current parent record.

## Index Helpers

### `getIndexItems(array $scopes, array $orders, array $filters, bool $forcePaginate): Paginator`

Fetches a paginated set of items using the repository. Applies scopes, ordering, and filters.

### `transformIndexItems(Collection $items): Collection`

Hook called after fetching index items. Override in subclasses to transform results before serialisation.

### `getFormattedIndexItems(Paginator $items): array`

Formats paginator results into the array structure expected by the frontend.

### `getJSONData(array $data): mixed`

Returns JSON-formatted index data with pagination meta.

## Form Helpers

### `validateFormRequest(array $formData): Request`

Validates form data respecting `$fieldsPermissions` — strips fields the current user is not allowed to write.

### `getFormRequestClass(array $formData): Request`

Resolves or instantiates the correct `FormRequest` class for the current module route.

## Route Helpers

### `getRoutePrefix(): string`

Returns the current route prefix string.

### `generateRoutePrefix(bool $withParent = true): string`

Builds the route prefix from module and route names, optionally including parent segments.

### `getModuleRoute(int $id, string $action, bool $absolute): string`

Generates a URL for a module action (e.g. edit, destroy) for a given record ID.

### `getReplaceUrl(): bool`

Returns `true` when the browser URL should be replaced (not pushed) after navigation.
