---
sidebarPos: 12
sidebarTitle: Media
---

# Media

**File**: `src/Entities/Media.php`  
**Namespace**: `Unusualify\Modularous\Entities`  
**Extends**: `Model`  
**Traits**: `HasFactory`, `HasCreator`

Represents an uploaded image in the media library. Stores dimensions, alt text, captions, and supports configurable extra metadata fields. Provides formatted output for frontend media pickers and integrates with the Glide image transformation service.

## Fillable Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `uuid` | `string` | UUID-based storage path |
| `filename` | `string` | Original filename |
| `alt_text` | `string` | Alt text for accessibility |
| `caption` | `string` | Image caption |
| `width` | `int` | Image width in pixels |
| `height` | `int` | Image height in pixels |

Additional fields from `modularous.media_library.extra_metadatas_fields` config are merged into fillable at construction time.

## Accessors

| Accessor | Type | Description |
|----------|------|-------------|
| `dimensions` | `string` | `"{width}x{height}"` |

## Methods

### `scopeUnused($query)`

Returns all media records not referenced in the `mediables` pivot table.

### `canDeleteSafely(): bool`

Returns `true` when the media is not referenced by any model.

### `isReferenced(): bool`

Returns `true` when the media is used by at least one model.

### `mediableFormat(): array`

Formats the image for the frontend media picker with thumbnail, medium, and original URLs, dimensions, tags, and CRUD action URLs.

### `getMetadata($name, $fallback = null): mixed`

Retrieves metadata from the mediable pivot, respecting locale fallback for translatable metadata fields.

### `replace($fields): void`

Updates the media record and recalculates crop coordinates in all referencing `mediables` rows when dimensions change.

### `altTextFrom($filename): string`

Generates alt text from a filename by stripping extensions and `@2x` suffixes, then converting to title case.

### `delete(): bool`

Only deletes if the media can be deleted safely (not referenced). Returns `false` otherwise.

## Related

- [MediaLibraryController](/system-reference/backend/http/controllers/media-library-controller) — manages media uploads
- [GlideController](/system-reference/backend/http/controllers/glide-controller) — serves transformed images
- [HasImages](/system-reference/backend/entity-traits/media/has-images) — attaches media to models
