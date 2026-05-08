---
sidebarPos: 7
sidebarTitle: ImageServiceDefaults
---

# ImageServiceDefaults

**File**: `src/Services/MediaLibrary/ImageServiceDefaults.php`

`ImageServiceDefaults` is a trait included by image service drivers to provide shared default implementations for two `ImageServiceInterface` methods that behave identically across all drivers.

## Methods

| Method | Returns | Description |
|--------|---------|-------------|
| `getSocialFallbackUrl(): string` | Config-based URL or local fallback | Returns `ImageService::getSocialUrl($id)` for the SEO default image if `seo.image_default_id` is set; otherwise returns `seo.image_local_fallback` |
| `getTransparentFallbackUrl(): string` | Base64 GIF data URI | Returns `data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7` — a 1×1 transparent pixel |

## Constants

The trait also defines the `$cropParamsKeys` property used by all drivers when extracting crop coordinates from params arrays:

```php
protected $cropParamsKeys = ['crop_x', 'crop_y', 'crop_w', 'crop_h'];
```

## Configuration

```php
// config/modularity.php
'seo' => [
    'image_default_id'     => null,           // UUID of the default social image
    'image_local_fallback' => '/img/og.jpg',  // static fallback path
],
```

## Usage

All built-in drivers (`Local`, `Glide`, `Imgix`, `TwicPics`) use this trait:

```php
class Glide implements ImageServiceInterface
{
    use ImageServiceDefaults;

    // getSocialFallbackUrl() and getTransparentFallbackUrl()
    // are provided by the trait — no need to implement them here
}
```
