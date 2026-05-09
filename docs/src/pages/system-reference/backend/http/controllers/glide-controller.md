---
sidebarPos: 13
sidebarTitle: GlideController
---

# GlideController

**File**: `src/Http/Controllers/GlideController.php`  
**Namespace**: `Unusualify\Modularous\Http\Controllers`

Single-action invokable controller that serves on-the-fly image transformations via the [Glide](https://glide.thephpleague.com/) library.

## Signature

```php
public function __invoke(string $path, Application $app): mixed
```

Accepts an image `$path` (relative to the configured source), applies transformation parameters from the query string, and streams the transformed image back to the browser.

## Transformation Parameters

Parameters are passed as query string values and processed by Glide:

| Parameter | Example | Description |
|-----------|---------|-------------|
| `w` | `?w=300` | Width in pixels |
| `h` | `?h=200` | Height in pixels |
| `fit` | `?fit=crop` | Fit mode (`contain`, `max`, `fill`, `crop`) |
| `q` | `?q=80` | JPEG/WebP quality (1–100) |
| `fm` | `?fm=webp` | Output format |

See the [Glide documentation](https://glide.thephpleague.com/2.0/api/quick-reference/) for the full parameter reference.

## URL Signing

Glide URLs are signed by default to prevent abuse. The signing key is taken from `modularous.glide.sign_key`. Requests with an invalid signature return a 403 response.

## Related

- [MediaLibraryController](./media-library-controller) — manages the source images served through Glide
