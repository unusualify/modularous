---
sidebarPos: 6
sidebarTitle: ImageService (Facade)
---

# ImageService

**File**: `src/Services/MediaLibrary/ImageService.php`  
**Extends**: `Illuminate\Support\Facades\Facade`  
**Bound as**: `imageService`

`ImageService` is the Laravel Facade that provides a static proxy to whichever `ImageServiceInterface` driver is bound in the service container. The active driver is determined by the `media_library.image_service` config value.

## Usage

```php
use Unusualify\Modularity\Services\MediaLibrary\ImageService;

$url  = ImageService::getUrl($media->uuid);
$lqip = ImageService::getLQIPUrl($media->uuid);
$crop = ImageService::getUrlWithCrop($media->uuid, [
    'crop_x' => 0,
    'crop_y' => 0,
    'crop_w' => 800,
    'crop_h' => 600,
]);
```

All methods on `ImageServiceInterface` are accessible statically through this Facade.

## Driver Selection

```php
// config/modularity.php
'media_library' => [
    'image_service' => env('IMAGE_SERVICE', 'local'),  // local | glide | imgix | twicpics
],
```

| Value | Driver |
|-------|--------|
| `local` | [Local](/system-reference/backend/services/media-library/local) |
| `glide` | [Glide](/system-reference/backend/services/media-library/glide) |
| `imgix` | [Imgix](/system-reference/backend/services/media-library/imgix) |
| `twicpics` | [TwicPics](/system-reference/backend/services/media-library/twicpics) |

## Swapping Drivers at Runtime

```php
// Temporarily use Imgix in a specific context
$url = app('imageService')->getUrl($id);      // uses configured driver

// Or resolve directly from the container
$imgix = app(\Unusualify\Modularity\Services\MediaLibrary\Imgix::class);
$url = $imgix->getUrl($id);
```
