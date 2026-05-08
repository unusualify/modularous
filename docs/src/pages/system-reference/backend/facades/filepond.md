---
sidebarPos: 4
sidebarTitle: Filepond
---

# Filepond

**Facade**: `Unusualify\Modularity\Facades\Filepond`  
**Accessor**: `Filepond`  
**Underlying**: `Unusualify\Modularity\Services\FilepondManager`

Provides the server-side handling for FilePond file uploads — processing temporary uploads, generating signed URLs, and managing the temporary file lifecycle.

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `process` | `(mixed $file): mixed` | Handles a FilePond `process` request; stores the file temporarily and returns the server ID |
| `validate` | `(mixed $file): bool` | Validates an uploaded file against configured rules |
| `generateTemporaryUrl` | `(string $path): string` | Generates a signed temporary URL for a stored file |
| `delete` | `(string $path): bool` | Removes a temporary file by its server path |
| `getServerConfig` | `(): array` | Returns the FilePond server config array for the frontend |

## Usage

```php
use Unusualify\Modularity\Facades\Filepond;

// In a FilePond process endpoint
public function process(Request $request)
{
    return Filepond::process($request->file('filepond'));
}

// Get server config to pass to the Vue component
$serverConfig = Filepond::getServerConfig();
```

## Notes

- The `getServerConfig()` return value is injected into the Inertia page props and consumed by the `ue-filepond` Vue component to configure upload endpoints automatically.
- Temporary files are stored in the disk defined by `modularity.filepond.disk` config (default: `local`).
- `flush:filepond` artisan command clears abandoned temporary files.
