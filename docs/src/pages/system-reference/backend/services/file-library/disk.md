---
sidebarPos: 2
sidebarTitle: Disk
---

# Disk

**File**: `src/Services/FileLibrary/Disk.php`  
**Implements**: `FileServiceInterface`  
**Config value**: `disk` (default driver)

The `Disk` driver is the default FileLibrary implementation. It delegates URL generation to Laravel's filesystem via `Storage::disk()->url()`, using whichever disk is configured under `file_library.disk`.

## Configuration

```php
// config/modularity.php
'file_library' => [
    'disk' => env('FILE_LIBRARY_DISK', 'public'),
],
```

Set `FILE_LIBRARY_DISK` to any Laravel filesystem disk name (`public`, `s3`, `local`, etc.).

## How It Works

```php
public function getUrl($id)
{
    return $this->filesystemManager
        ->disk(config('modularity.file_library.disk'))
        ->url($id);
}
```

The driver reads the configured disk name at call time, then calls Laravel's standard `disk()->url($id)` — the same mechanism used for storage links. This means the URL format (relative vs absolute, with or without CDN prefix) is determined entirely by the disk's own driver configuration.

## Usage

```php
use Unusualify\Modularity\Facades\FileService;

// Store a file and record its path
$path = Storage::disk('public')->putFile('documents', $request->file('doc'));

// Retrieve its public URL later
$url = FileService::getUrl($path);
```

## Notes

- The driver does not apply any transformations to the file.
- For S3 or other cloud disks, ensure the disk is configured with a public visibility or a CDN URL in `filesystems.disks.{name}.url`.
- To swap the driver, bind a different `FileServiceInterface` implementation to `fileService` in a service provider.
