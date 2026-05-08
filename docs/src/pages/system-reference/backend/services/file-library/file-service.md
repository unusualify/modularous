---
sidebarPos: 1
sidebarTitle: FileServiceInterface
---

# FileServiceInterface

**File**: `src/Services/FileLibrary/FileServiceInterface.php`

Contract that all FileLibrary drivers must implement. Currently defines a single method for retrieving a file URL by its stored identifier.

## Interface Definition

```php
interface FileServiceInterface
{
    public function getUrl(string $id): string;
}
```

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `getUrl` | `getUrl($id): string` | Returns the public URL for the file identified by `$id` |

## Implementing a Custom Driver

```php
use Unusualify\Modularity\Services\FileLibrary\FileServiceInterface;

class MyCloudStorage implements FileServiceInterface
{
    public function getUrl($id): string
    {
        return 'https://cdn.example.com/files/' . $id;
    }
}
```

Register the custom driver in a service provider:

```php
$this->app->bind('fileService', MyCloudStorage::class);
```

## Facade

The `FileService` Laravel Facade resolves to whatever class is bound to `fileService` in the container. The default binding is the [Disk](/system-reference/backend/services/file-library/disk) driver.

```php
use Unusualify\Modularity\Facades\FileService;

$url = FileService::getUrl($file->uuid);
```
