---
sidebarPos: 5
sidebarTitle: ImageServiceInterface
---

# ImageServiceInterface

**File**: `src/Services/MediaLibrary/ImageServiceInterface.php`

Contract that all MediaLibrary drivers must implement. Defines the full set of URL-generating methods that the rest of the framework calls, regardless of which image service is active.

## Interface Definition

```php
interface ImageServiceInterface
{
    public function getUrl($id, array $params = []);
    public function getUrlWithCrop($id, array $crop_params, array $params = []);
    public function getUrlWithFocalCrop($id, array $cropParams, $width, $height, array $params = []);
    public function getLQIPUrl($id, array $params = []);
    public function getSocialUrl($id, array $params = []);
    public function getCmsUrl($id, array $params = []);
    public function getRawUrl($id);
    public function getDimensions($id);
    public function getSocialFallbackUrl();
    public function getTransparentFallbackUrl();
}
```

## Methods

| Method | Description |
|--------|-------------|
| `getUrl($id, $params)` | Standard URL with optional transformation parameters |
| `getUrlWithCrop($id, $crop_params, $params)` | URL with a manual crop rectangle (`crop_x`, `crop_y`, `crop_w`, `crop_h`) |
| `getUrlWithFocalCrop($id, $cropParams, $width, $height, $params)` | URL with focal-point crop — preserves the subject during responsive resizing |
| `getLQIPUrl($id, $params)` | Low-Quality Image Placeholder URL (small, blurred preview) |
| `getSocialUrl($id, $params)` | URL optimised for social sharing (typically 1200×630) |
| `getCmsUrl($id, $params)` | URL sized for CMS thumbnails (typically 240×180) |
| `getRawUrl($id)` | Original file URL with no transformation parameters |
| `getDimensions($id)` | Returns `['width' => int, 'height' => int]` or `null` if unsupported |
| `getSocialFallbackUrl()` | Default social image URL when no media is attached |
| `getTransparentFallbackUrl()` | 1×1 transparent GIF data URI — safe placeholder |

## Built-in Implementations

| Driver | Config value |
|--------|-------------|
| [Local](/system-reference/backend/services/media-library/local) | `local` |
| [Glide](/system-reference/backend/services/media-library/glide) | `glide` |
| [Imgix](/system-reference/backend/services/media-library/imgix) | `imgix` |
| [TwicPics](/system-reference/backend/services/media-library/twicpics) | `twicpics` |

## Implementing a Custom Driver

```php
use Unusualify\Modularous\Services\MediaLibrary\ImageServiceInterface;

class CloudflareImages implements ImageServiceInterface
{
    public function getUrl($id, array $params = []): string
    {
        return "https://imagedelivery.net/account/{$id}/public";
    }

    // ... implement remaining methods
}
```

Register in a service provider:

```php
$this->app->bind('imageService', CloudflareImages::class);
```
