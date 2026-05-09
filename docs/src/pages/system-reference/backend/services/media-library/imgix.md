---
sidebarPos: 3
sidebarTitle: Imgix
---

# Imgix

**File**: `src/Services/MediaLibrary/Imgix.php`  
**Config value**: `imgix`  
**Package**: `imgix/imgix-php`

The `Imgix` driver generates [Imgix](https://imgix.com/) CDN URLs with optional HMAC signing. Transformations are applied by the Imgix CDN on the first request and cached at the edge.

## Configuration

```php
// config/modularous.php
'imgix' => [
    'source_host'         => env('IMGIX_SOURCE_HOST', 'your-source.imgix.net'),
    'use_https'           => true,
    'use_signed_urls'     => false,
    'sign_key'            => env('IMGIX_SIGN_KEY'),
    'default_params'      => ['auto' => 'format,compress', 'q' => 80],
    'lqip_default_params' => ['w' => 20, 'blur' => 200, 'q' => 1],
    'social_default_params' => ['w' => 1200, 'h' => 630, 'fit' => 'crop'],
    'cms_default_params'  => ['w' => 240, 'h' => 180, 'fit' => 'crop'],
    'add_params_to_svgs'  => false,
],
```

## Signed URLs

When `use_signed_urls = true`, every generated URL is HMAC-signed using the `sign_key`. Imgix validates the signature before serving the image, preventing URL manipulation.

## Crop & Focal Point

`getUrlWithCrop()` converts crop coordinates to Imgix's `rect` parameter:

```
rect=x,y,w,h
```

`getUrlWithFocalCrop()` converts coordinates to Imgix's focal point parameters:

```
fp-x=0.50&fp-y=0.30&fp-z=1.5&crop=focalpoint&fit=crop
```

## Dimensions

`getDimensions()` fetches the image metadata by requesting the URL with `?fm=json`, then reads `PixelWidth` and `PixelHeight` from the JSON response.

## SVG Handling

When `add_params_to_svgs = false` (default), SVG files are served via `getRawUrl()` without any transformation parameters.

## Common URL Parameters

Imgix supports all standard [Imgix URL API parameters](https://docs.imgix.com/apis/url). Common ones:

| Param | Example | Effect |
|-------|---------|--------|
| `w` | `w=300` | Width |
| `h` | `h=200` | Height |
| `fit` | `fit=crop` | Resize mode |
| `auto` | `auto=format` | Auto format selection (WebP where supported) |
| `q` | `q=80` | Quality |
| `rect` | `rect=0,0,300,200` | Crop rectangle |
