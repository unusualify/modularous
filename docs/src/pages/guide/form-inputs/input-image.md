---
sidebarPos: 19
sidebarTitle: Image
---

# Image

The `image` input type renders `VInputImage`, an image picker backed by the Modularous media library. It shows thumbnails for each selected image, supports crop previews, download, delete, and can display multiple images in a grid layout.

## Hydrate

**Class:** `ImageHydrate`
**Config type:** `image`
**Output type:** `input-image` → `VInputImage`

The hydrate sets `type` to `input-image` and defaults `label` to the translated string `"Images"`.

## Usage

### Single image

```php
[
    'type'  => 'image',
    'name'  => 'cover',
    'label' => 'Cover Image',
    'max'   => 1,
]
```

### Gallery (multiple images)

```php
[
    'type'  => 'image',
    'name'  => 'gallery',
    'label' => 'Gallery',
    'max'   => 10,
]
```

### Translated images

```php
[
    'type'       => 'image',
    'name'       => 'images',
    'translated' => true,
]
```

### Custom grid columns

The `imageCol` prop controls the column layout of the image grid. Defaults to `{cols: 12, md: 6, lg: 4}`.

```php
[
    'type'     => 'image',
    'name'     => 'photos',
    'imageCol' => ['cols' => 12, 'md' => 4, 'lg' => 3],
]
```

## Schema Defaults

| Key | Default | Description |
|-----|---------|-------------|
| `name` | `'images'` | Form field name |
| `translated` | `false` | Whether images are locale-specific |
| `default` | `[]` | Empty image list |
| `label` | `'Images'` | Auto-translated label |

## See Also

- [File](/guide/form-inputs/input-file) — Non-image media library attachments
- [Filepond](/guide/form-inputs/input-filepond) — Alternative upload using the Filepond protocol
- [Hydrates reference](/system-reference/hydrates) — Resolution table and schema contract
