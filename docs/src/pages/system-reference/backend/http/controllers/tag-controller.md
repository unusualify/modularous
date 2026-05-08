---
sidebarPos: 21
sidebarTitle: TagController
---

# TagController

**File**: `src/Http/Controllers/TagController.php`  
**Namespace**: `Unusualify\Modularity\Http\Controllers`  
**Extends**: `Illuminate\Routing\Controller`

Provides tag search and creation endpoints used by taggable input components across the admin panel.

## Methods

### `index(Request $request): JsonResponse`

Searches existing tags for a given taggable model.

**Request parameters**:

| Parameter | Type | Description |
|-----------|------|-------------|
| `q` | `string` | Search query (partial name match) |
| `taggable` | `string` | Fully qualified model class (e.g. `Modules\Blog\Models\Post`) |

Returns a list of matching tags for the specified model type.

### `update(Request $request): JsonResponse`

Creates a new tag or retrieves an existing one by value.

**Request parameters**:

| Parameter | Type | Description |
|-----------|------|-------------|
| `value` | `string` | Tag display name |
| `taggable` | `string` | Fully qualified model class |
| `locale` | `string` | Locale for the tag name (optional) |

Generates a slug via the tag model's slug method. Returns the tag record (created or found).

## Usage in Forms

Tag input fields in the admin panel call `index` for autocomplete and `update` when the user types a new tag value that doesn't exist yet. The `taggable` parameter scopes tags to the specific model so different models don't share tag namespaces unless intentionally configured.

## Related

- `HasTags` entity trait — adds tagging support to a model
- `CoreController::tags()` / `tagsUpdate()` — module-specific tag endpoints that delegate to this controller
