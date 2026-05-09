---
sidebarPos: 5
sidebarTitle: HandleInertiaRequests
---

# HandleInertiaRequests

**File**: `src/Http/Middleware/HandleInertiaRequests.php`  
**Alias**: `inertia.middleware` (singleton)  
**Extends**: `Inertia\Middleware`

The central Inertia middleware that establishes the root Blade view and populates the shared props passed to every Vue page component on every request.

## Root View

```php
protected $rootView = 'modularous::layouts.app-inertia';
```

## Shared Props

All props returned by `share()` are available in every Vue page via `usePage().props`:

### `auth`

```js
{ user: { ...userObject } }
```

### `flash`

Lazy-loaded flash messages from the session:

```js
{ message: '...', success: '...', error: '...' }
```

### `config`

```js
{ app_name: '...', js_namespace: '...', timezone: '...' }
```

### `endpoints`

Custom per-request endpoint map set via `$request->attributes->set('endpoints', [...])` in controllers.

### `authorization`

Resolved per request from the authenticated user:

| Key | Type | Description |
|-----|------|-------------|
| `isSuperAdmin` | bool | `$user->is_superadmin` |
| `isClient` | bool | `$user->isClient()` |
| `is_client` | bool | `$user->is_client` |
| `hasRestorable` | bool | Soft-delete restore capability |
| `hasBulkable` | bool | Bulk action capability |
| `permissions` | string[] | All permission names |
| `roles` | string[] | All role names |

Returns `[]` for unauthenticated requests.

### `storeData`

Initialisation data for the Pinia/Vuex store, split into sub-objects:

| Key | Contents |
|-----|----------|
| `config` | Sidebar, topbar, bottomNav options; UI preferences; endpoints |
| `user` | Profile, routes, shortcut schemas |
| `medias` | Media library types (images + files), crop config |
| `languages` | All languages, active language |
| `form` | Base URL, initial inputs |
| `datatable` | Advanced filters, custom modal flag |
| `ambient` | Environment, app name/email/debug, package versions |

## Media Types

`getMediaTypes()` adds entries for enabled libraries:

| Module flag | Entry added |
|-------------|------------|
| `enabled.media-library` | Images type with `media-library.media.index` endpoint |
| `enabled.file-library` | Files type with `file-library.file.index` endpoint |

## Notes

- `version()` delegates to the parent Inertia middleware, which hashes the `mix-manifest.json` or Vite manifest for asset versioning.
- `getLanguages()` and `getActiveLanguage()` return empty arrays by default — override in an app-level middleware extension to plug in your translation/locale system.
