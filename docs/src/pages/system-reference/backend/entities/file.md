---
sidebarPos: 10
sidebarTitle: File
---

# File

**File**: `src/Entities/File.php`  
**Namespace**: `Unusualify\Modularous\Entities`  
**Extends**: `Model`  
**Traits**: `HasFactory`, `HasCreator`

Represents an uploaded non-image file in the file library. Stores the file's UUID-based storage path, original filename, and byte size.

## Fillable Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `uuid` | `string` | UUID-based storage path |
| `filename` | `string` | Original filename |
| `size` | `int` | File size in bytes |

## Accessors

| Accessor | Type | Description |
|----------|------|-------------|
| `size_for_human` | `string` | Human-readable size (e.g. `"2.4 MB"`) |
| `size_in_mb` | `float` | Size in megabytes |

## Methods

### `canDeleteSafely(): bool`

Returns `true` when the file is not referenced in the `fileables` pivot table.

### `scopeUnused($query)`

Returns all files not referenced in any `fileables` record.

### `mediableFormat(): array`

Formats the file into the structure expected by the frontend: `id`, `name`, `src`, `original`, `size`, `filesizeInMb`.

## Related

- [FileLibraryController](/system-reference/backend/http/controllers/file-library-controller) — manages file uploads and library
- [HasFiles](/system-reference/backend/entity-traits/media/has-files) — attaches files to models
