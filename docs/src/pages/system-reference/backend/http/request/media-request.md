---
sidebarPos: 5
sidebarTitle: MediaRequest
---

# MediaRequest

**File**: `src/Http/Requests/MediaRequest.php`
**Namespace**: `Unusualify\Modularous\Http\Requests`
**Extends**: [`BaseFormRequest`](./base-form-request)

The current media-library request is effectively a pass-through: it authorizes everything and performs no validation.

## Current behaviour

| Method | Behaviour |
|--------|-----------|
| `authorize()` | Returns `true` |
| `rules()` | Commented out — inherits the method-dispatched default from [`BaseFormRequest`](./base-form-request), which resolves to an empty array for every verb |

## Dormant rules

The source carries a commented-out block that mirrors the endpoint-branching pattern from [`FileRequest`](./file-request):

```php
// switch (config('twill.media_library.endpoint_type')) {
//     case 'local': return ['qqfilename' => 'required', 'qqfile' => 'required'];
//     case 'azure': return ['blob' => 'required', 'name' => 'required'];
//     case 's3':
//     default:      return ['key' => 'required', 'name' => 'required'];
// }
```

It is retained as a reference but is intentionally disabled. Re-enable it if you need strict validation on uploads — remember to switch the config key to `modularous.media_library.endpoint_type` rather than the legacy `twill.*` path.

## Related

- [`MediaLibraryController`](/system-reference/backend/http/controllers/media-library-controller) — consumes this request
- [Services · MediaLibrary](/system-reference/backend/services/media-library/overview)
