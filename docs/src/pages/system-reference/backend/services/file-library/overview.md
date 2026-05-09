---
sidebarPos: 4
sidebarTitle: Overview
---

# FileLibrary Services

**Directory**: `src/Services/FileLibrary/`  
**Facade**: `Unusualify\Modularous\Facades\FileService` (bound as `fileService`)

The FileLibrary namespace provides a storage abstraction for **non-image file assets** — PDFs, documents, spreadsheets, and other binary files. It mirrors the MediaLibrary pattern with an interface + driver architecture.

## Classes

| Class | Description | Page |
|-------|-------------|------|
| [FileServiceInterface](/system-reference/backend/services/file-library/file-service) | Contract that all file storage drivers must implement | — |
| [FileService](/system-reference/backend/services/file-library/file-service) | Laravel Facade resolving to the `fileService` binding | — |
| [Disk](/system-reference/backend/services/file-library/disk) | Default driver — serves files from a configured Laravel disk | [Disk →](/system-reference/backend/services/file-library/disk) |

## Configuration

```php
// config/modularous.php
'file_library' => [
    'disk' => env('FILE_LIBRARY_DISK', 'public'),
],
```

## Facade Usage

```php
use Unusualify\Modularous\Services\FileLibrary\FileService;

$url = FileService::getUrl($file->uuid);
```
