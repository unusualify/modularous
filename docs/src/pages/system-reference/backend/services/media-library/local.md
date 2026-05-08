---
sidebarPos: 1
sidebarTitle: Local
---

# Local

**File**: `src/Services/MediaLibrary/Local.php`  
**Config value**: `local`

The `Local` driver serves images directly from the configured Laravel storage disk via `Storage::url()`. It does **not** apply any transformations — all URL-generating methods return the raw storage URL regardless of the parameters passed.

## When to Use

- Local development environments
- Applications that don't need server-side image transformation
- When images are pre-processed before storage

## Configuration

```php
// config/modularity.php
'media_library' => [
    'image_service' => 'local',
    'disk'          => 'public',   // any Laravel filesystem disk
],
```

## Behaviour

All interface methods (`getUrl`, `getUrlWithCrop`, `getUrlWithFocalCrop`, `getLQIPUrl`, `getSocialUrl`, `getCmsUrl`) delegate to `getRawUrl()`, which returns:

```php
Storage::disk(config('modularity.media_library.disk'))->url($id)
```

`getDimensions()` is not supported and returns `null`.

## Notes

- Since no transformation is applied, passing `$params` or `$cropParams` has no effect.
- LQIP and social URLs are identical to the full-resolution URL.
- Suitable as a fallback driver when no CDN is configured.
