---
sidebarPos: 5
sidebarTitle: Overview
---

# MediaLibrary Services

**Directory**: `src/Services/MediaLibrary/`  
**Facade**: `Unusualify\Modularity\Facades\ImageService` (bound as `imageService`)

The MediaLibrary namespace provides a **driver-based image service abstraction**. All concrete drivers implement `ImageServiceInterface`, exposing a unified API for generating image URLs regardless of the underlying CDN or storage backend.

## Selecting a Driver

Set `modularity.media_library.image_service` in `config/modularity.php`:

```php
'media_library' => [
    'image_service' => env('MEDIA_LIBRARY_IMAGE_SERVICE', 'local'),
    'disk'          => env('MEDIA_LIBRARY_DISK', 'public'),
],
```

| Driver | Config value | Page |
|--------|-------------|------|
| [Local](/system-reference/backend/services/media-library/local) | `local` | Direct disk URL — no transformation |
| [Glide](/system-reference/backend/services/media-library/glide) | `glide` | On-the-fly server-side transformation via League/Glide |
| [Imgix](/system-reference/backend/services/media-library/imgix) | `imgix` | Imgix CDN with signed/unsigned URL generation |
| [TwicPics](/system-reference/backend/services/media-library/twicpics) | `twicpics` | TwicPics CDN with transformation string |

## Supporting Classes

| Class | Description | Page |
|-------|-------------|------|
| [ImageServiceInterface](/system-reference/backend/services/media-library/image-service-interface) | Contract all drivers must implement | [→](/system-reference/backend/services/media-library/image-service-interface) |
| [ImageService](/system-reference/backend/services/media-library/image-service) | Laravel Facade resolving to the active driver | [→](/system-reference/backend/services/media-library/image-service) |
| [ImageServiceDefaults](/system-reference/backend/services/media-library/image-service-defaults) | Shared trait: `getSocialFallbackUrl`, `getTransparentFallbackUrl` | [→](/system-reference/backend/services/media-library/image-service-defaults) |
| [AbstractParamsProcessor](/system-reference/backend/services/media-library/abstract-params-processor) | Base class for driver-specific parameter translators | [→](/system-reference/backend/services/media-library/abstract-params-processor) |
| [TwicPicsParamsProcessor](/system-reference/backend/services/media-library/twicpics-params-processor) | Translates standard params to TwicPics transformation syntax | [→](/system-reference/backend/services/media-library/twicpics-params-processor) |

## ImageServiceInterface

All drivers implement the following interface:

| Method | Signature | Description |
|--------|-----------|-------------|
| `getUrl` | `getUrl(string $id, array $params): string` | Standard URL, applying default transformation params |
| `getUrlWithCrop` | `getUrlWithCrop(string $id, array $cropParams, array $params): string` | URL with explicit crop coordinates (`crop_x`, `crop_y`, `crop_w`, `crop_h`) |
| `getUrlWithFocalCrop` | `getUrlWithFocalCrop(string $id, array $cropParams, int $width, int $height, array $params): string` | URL using focal-point crop calculated from crop coordinates and original dimensions |
| `getLQIPUrl` | `getLQIPUrl(string $id, array $params): string` | Low-quality image placeholder URL (small, blurry preview) |
| `getSocialUrl` | `getSocialUrl(string $id, array $params): string` | Open Graph / social sharing optimized URL |
| `getCmsUrl` | `getCmsUrl(string $id, array $params): string` | Admin panel thumbnail URL |
| `getRawUrl` | `getRawUrl(string $id): string` | Unmodified source URL without any transformation params |
| `getDimensions` | `getDimensions(string $id): ?array` | Return `['width' => int, 'height' => int]` or `null` if unsupported |

## Facade Usage

```php
use Unusualify\Modularity\Services\MediaLibrary\ImageService;

// Via facade (resolves the active driver)
$url     = ImageService::getUrl($media->uuid);
$lqip    = ImageService::getLQIPUrl($media->uuid);
$cropped = ImageService::getUrlWithCrop($media->uuid, $media->crop_params);
```
