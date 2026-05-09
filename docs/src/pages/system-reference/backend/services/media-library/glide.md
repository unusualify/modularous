---
sidebarPos: 2
sidebarTitle: Glide
---

# Glide

**File**: `src/Services/MediaLibrary/Glide.php`  
**Config value**: `glide`  
**Package**: `league/glide`

The `Glide` driver provides **server-side, on-the-fly image transformation** using [League Glide](https://glide.thephpleague.com/). Crop, resize, format conversion, and quality adjustments are applied by the Glide server on each request and served through a dedicated route.

## Configuration

```php
// config/modularous.php
'media_library' => [
    'image_service' => 'glide',
],

'glide' => [
    'base_url'           => null,               // defaults to app.url
    'base_path'          => 'img',              // URL path prefix for the Glide endpoint
    'source'             => storage_path('app/public'),
    'cache'              => storage_path('app/glide_cache'),
    'cache_path_prefix'  => '.cache',
    'use_signed_urls'    => false,
    'sign_key'           => env('GLIDE_SIGN_KEY'),
    'driver'             => 'gd',               // gd | imagick
    'presets'            => [],
    'default_params'     => ['q' => 90, 'fm' => 'webp'],
    'lqip_default_params'=> ['w' => 20, 'blur' => 1],
    'social_default_params' => ['w' => 1200, 'h' => 630, 'fit' => 'crop'],
    'cms_default_params' => ['w' => 240, 'h' => 180, 'fit' => 'crop'],
    'original_media_for_extensions' => ['svg', 'gif'],
    'add_params_to_svgs'  => false,
],
```

## Signed URLs

When `use_signed_urls = true`, the service signs every generated URL using the `sign_key`. The Glide render endpoint validates the signature and returns HTTP 403 if it is invalid. This prevents URL manipulation attacks.

```php
'use_signed_urls' => true,
'sign_key'        => env('GLIDE_SIGN_KEY', 'a-secure-random-string'),
```

## Render Endpoint

Register the Glide render route so the server can handle transformation requests:

```php
Route::get('/img/{path}', function ($path) {
    return app(\Unusualify\Modularous\Services\MediaLibrary\Glide::class)->render($path);
})->where('path', '.*');
```

`render($path)` validates the signature (if enabled) and streams the transformed image.

## Presets

Define reusable transformation presets in config:

```php
'presets' => [
    'thumbnail' => ['w' => 300, 'h' => 300, 'fit' => 'crop'],
    'banner'    => ['w' => 1200, 'h' => 400, 'fit' => 'crop'],
],
```

Access a preset URL with `getPresetUrl($id, 'thumbnail')`.

## URL Parameters

Glide uses standard Glide/Intervention query parameters:

| Param | Example | Effect |
|-------|---------|--------|
| `w` | `w=300` | Width in pixels |
| `h` | `h=200` | Height in pixels |
| `fit` | `fit=crop` | Resize mode: `crop`, `contain`, `fill`, etc. |
| `q` | `q=80` | Quality (1–100) |
| `fm` | `fm=webp` | Output format: `jpg`, `png`, `webp`, `gif` |
| `blur` | `blur=1` | Blur radius (used for LQIP) |
| `crop` | `crop=w,h,x,y` | Manual crop rectangle |

## Crop & Focal Point

`getUrlWithCrop()` accepts `crop_x`, `crop_y`, `crop_w`, `crop_h` and converts them to Glide's `crop` parameter format.

`getUrlWithFocalCrop()` converts crop coordinates + original dimensions to Glide's `fit=crop-{fpX}-{fpY}-{fpZ}` focal-point syntax.
