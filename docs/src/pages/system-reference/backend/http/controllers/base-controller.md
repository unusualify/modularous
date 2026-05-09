---
sidebarPos: 4
sidebarTitle: BaseController
---

# BaseController

**File**: `src/Http/Controllers/BaseController.php`  
**Namespace**: `Unusualify\Modularous\Http\Controllers`  
**Extends**: `PanelController`  
**Traits**: `ManageIndexAjax`, `ManagePrevious`, `ManageUtilities`, `ManageSingleton`, `ManageInertia`, `ManageTranslations`

Standard CRUD controller for the Modularous admin panel. Generated module controllers extend this class and inherit all of the actions below. Override any method in the generated controller to customise behaviour for that module.

## Constructor

```php
public function __construct(Application $app, Request $request)
```

Calls `PanelController::__construct()` and sets `$viewPrefix` to `{module}::{route}` (e.g. `blog::posts`).

## Properties

| Property | Type | Description |
|----------|------|-------------|
| `$viewPrefix` | `string` | Blade/Inertia view namespace (e.g. `blog::posts`) |
| `$titleFormKey` | `string` | Model attribute used as title in form headers |

## CRUD Actions

### `index($parentId = null): View|JsonResponse`

Lists all resources. Returns AJAX-formatted JSON when requested via `X-Requested-With: XMLHttpRequest`, otherwise renders the index view. Respects scopes, filters, and ordering from `PanelController`.

### `create($parentId = null): JsonResponse|RedirectResponse|View`

Shows the create form. Returns the form schema, field definitions, and relationship data. Redirects to index if the `create` index option is disabled.

### `store($parentId = null): JsonResponse|RedirectResponse`

Validates the request via `validateFormRequest()` then calls `$repository->create()`. On success:

- Logs activity (`created`).
- Respects `cmsSaveType` header to redirect to `save-close` (index) or `save-new` (create form).
- Returns JSON for Inertia requests.

### `show($id, $submoduleId = null): RedirectResponse|JsonResponse|View`

Fetches and displays a single resource. Redirects to `edit` if the module config does not define a show view.

### `edit($id): JsonResponse|RedirectResponse|View`

Shows the edit form for a resource. Loads the form schema, relationships, and current field values. Redirects to index if the `edit` option is disabled.

### `update($id, $submoduleId = null): JsonResponse|RedirectResponse`

Validates then calls `$repository->update()`. On success:

- Logs activity (`updated`).
- Respects `cmsSaveType` for redirect target.

### `destroy($id, $submoduleId = null): JsonResponse`

Soft-deletes the resource via `$repository->delete()`. Returns a JSON success/error response.

### `forceDelete(): JsonResponse`

Permanently deletes a soft-deleted resource. Requires the `forceDelete` index option to be enabled.

### `restore(): JsonResponse`

Restores a soft-deleted resource. Requires the `restore` index option to be enabled.

### `duplicate($id, $submoduleId = null): JsonResponse`

Duplicates the resource by cloning it and its relationships. Logs a `duplicated` activity event.

## Bulk Actions

### `bulkDelete(): JsonResponse`

Soft-deletes multiple resources by IDs.

### `bulkForceDelete(): JsonResponse`

Permanently deletes multiple resources by IDs.

### `bulkRestore(): JsonResponse`

Restores multiple soft-deleted resources by IDs.

### `reorder(): JsonResponse`

Updates the sort position of multiple resources given an ordered array of IDs.

## Save Type Behaviour

The frontend sends a `cmsSaveType` value with every save request to control where the user is redirected after the operation:

| `cmsSaveType` | Redirect target |
|---------------|-----------------|
| `save-close` | Index listing |
| `save-new` | Create form |
| (default) | Edit form for the saved record |

## Translation Fallback Chain

User-facing messages (created, updated, deleted, etc.) are resolved in this order:

1. `{module}::{routeName}.{action}` translation key.
2. `{module}::generic.{action}` translation key.
3. Modularous default message.

## View Rendering

When the `ManageInertia` trait is active (detected from module config), responses are rendered via Inertia instead of Blade. The view prefix `{module}::{route}` is used for both.
