---
sidebarPos: 4
sidebarTitle: TwicPics
---

# TwicPics

**File**: `src/Services/MediaLibrary/TwicPics.php`  
**Params Processor**: `src/Services/MediaLibrary/TwicPicsParamsProcessor.php`  
**Config value**: `twicpics`

The `TwicPics` driver generates [TwicPics](https://www.twicpics.com/) CDN URLs. Transformation parameters are built by `TwicPicsParamsProcessor` and appended to the CDN URL path using TwicPics' transformation string syntax.

## Configuration

```php
// config/modularous.php
'twicpics' => [
    'base_url'            => env('TWICPICS_BASE_URL', 'https://your-domain.twic.pics'),
    'default_params'      => [],
    'lqip_default_params' => ['output' => 'preview'],
    'social_default_params' => ['resize' => '1200x630'],
    'cms_default_params'  => ['resize' => '240x180'],
],
```

## TwicPicsParamsProcessor

`TwicPicsParamsProcessor` extends `AbstractParamsProcessor` and translates the standard Modularous crop/resize param arrays into TwicPics' transformation string format.

TwicPics uses a path-based transformation syntax:

```
https://your-domain.twic.pics/path/to/image.jpg?twic=v1/resize=300x200
```

The processor builds the `?twic=v1/...` portion from the provided params.

## Crop & Focal Point

TwicPics supports crop via its `focus` and `crop` transformations. `getUrlWithCrop()` and `getUrlWithFocalCrop()` pass the crop coordinates through the params processor to generate the correct transformation string.

## LQIP

`getLQIPUrl()` uses `lqip_default_params` (default: `['output' => 'preview']`), which instructs TwicPics to return a low-resolution preview version of the image.
