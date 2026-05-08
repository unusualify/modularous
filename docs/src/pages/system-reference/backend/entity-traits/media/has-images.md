---
sidebarPos: 3
sidebarTitle: HasImages
---

# HasImages

**Namespace**: `Unusualify\Modularity\Entities\Traits\HasImages`

Attaches images from the `Media` model via a `MorphToMany` through the `modularity_mediables` pivot table. Handles crop variants, alt text, captions, video URLs, LQIP placeholders, and social images.

---

## Boot Behavior

| Event | Action |
|-------|--------|
| `deleted` / `forceDeleting` | Detaches all media from the pivot table |
| `retrieved` | Sets the `_icon` attribute if an `image` input with `isIcon: true` is found in route inputs |
| `updating` / `saving` | Removes `_icon` from dirty attributes before persisting |

---

## Relationship

```php
public function medias(): MorphToMany
```

Pivot columns: `crop`, `role`, `crop_w`, `crop_h`, `crop_x`, `crop_y`, `lqip_data`, `ratio`, `metadatas` (+ `locale` when translated form fields are enabled).

---

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `hasImage` | `(string $role, string $crop = 'default'): bool` | Returns `true` if an image is attached for the given role and crop |
| `image` | `(string $role, string $crop = 'default', array $params = [], bool $has_fallback = false, bool $cms = false): ?string` | Returns the image URL for a role/crop; applies transform params |
| `images` | `(string $role, string $crop = 'default', array $params = [], ?string $locale = null): array` | Returns all image URLs for a role |
| `imagesWithCrops` | `(string $role, array $params = []): array` | Returns all image URLs grouped by `media_id` → `crop` |
| `imageAsArray` | `(string $role, string $crop = 'default', array $params = []): array` | Returns a structured array with `src`, `width`, `height`, `alt`, `caption`, `video` |
| `imagesAsArrays` | `(string $role, string $crop = 'default', array $params = []): array` | Same as `imageAsArray` for all images in a role |
| `imagesAsArraysWithCrops` | `(string $role, array $params = []): array` | All images as arrays, grouped by media ID and crop |
| `imageAltText` | `(string $role, ?Media $media = null): string` | Returns the `altText` metadata for the image |
| `imageCaption` | `(string $role, ?Media $media = null): string` | Returns the `caption` metadata |
| `imageVideo` | `(string $role, ?Media $media = null): string` | Returns the `video` metadata URL |
| `imageObject` | `(string $role, string $crop = 'default'): ?Media` | Returns the raw `Media` model |
| `imageObjects` | `(string $role, string $crop = 'default'): Collection` | Returns all `Media` models for a role/crop |
| `cmsImage` | `(string $role, string $crop = 'default', array $params = []): string` | CMS-optimized URL (uses `ImageService::getCmsUrl`) |
| `defaultCmsImage` | `(array $params = []): string` | CMS URL of the first attached media regardless of role |
| `socialImage` | `(string $role, string $crop = 'default', array $params = [], bool $has_fallback = false): ?string` | Social media–optimised URL |
| `lowQualityImagePlaceholder` | `(string $role, string $crop = 'default', array $params = [], bool $has_fallback = false): ?string` | Base64 LQIP string for progressive loading |

---

## Configuration

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `cropParamsKeys` | `array` | `['crop_x','crop_y','crop_w','crop_h']` | Pivot columns extracted for crop transforms |

Set `media_library.translated_form_fields = true` in `modularity.php` to enable per-locale media.

---

## Usage

```php
use Unusualify\Modularity\Entities\Traits\HasImages;

class Article extends Model
{
    use HasImages;
}

// Basic URL retrieval
$article->image('cover');                              // default crop
$article->image('cover', 'thumbnail');                 // named crop
$article->image('cover', 'default', ['w' => 800]);     // with transform params

// Multiple images
$article->images('gallery');                           // array of URLs
$article->imagesWithCrops('hero');                     // grouped by crop

// Structured data for frontend
$article->imageAsArray('hero');
// ['src' => '...', 'width' => 1200, 'height' => 630, 'alt' => '...', 'caption' => '...', 'video' => '']

// Metadata
$article->imageAltText('cover');
$article->imageCaption('cover');

// Special URLs
$article->cmsImage('thumbnail');
$article->socialImage('og');
$article->lowQualityImagePlaceholder('hero');

// Existence check
if ($article->hasImage('cover')) { ... }
```
