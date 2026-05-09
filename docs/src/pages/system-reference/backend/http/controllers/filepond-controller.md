---
sidebarPos: 11
sidebarTitle: FilepondController
---

# FilepondController

**File**: `src/Http/Controllers/FilepondController.php`  
**Namespace**: `Unusualify\Modularous\Http\Controllers`  
**Extends**: `App\Http\Controllers\Controller`

Handles the temporary file lifecycle for [FilePond](https://pqina.nl/filepond/) uploads. Files are held in a temporary location and either promoted to the media/file library on form submission or reverted (deleted) when the user cancels.

## Constructor

```php
public function __construct(FilepondManager $manager)
```

## Methods

### `upload(Request $request): Response`

Accepts a FilePond upload, stores the file in the configured temporary directory, and returns the temporary server ID string. The ID is what FilePond sends back to the server on form submission.

### `revert(Request $request): Response`

Deletes a previously uploaded temporary file. Called by FilePond when the user removes a file before submitting the form.

### `preview(Request $request, string $folder): Response`

Streams a temporary file for preview. The `$folder` parameter scopes the lookup to a specific upload folder, preventing path traversal.

## Temporary File Flow

```
User picks file
  └─ FilePond POSTs to /filepond/upload
       └─ FilepondController::upload() saves to tmp/, returns server ID
            └─ User submits form
                 ├─ Server resolves ID → permanent storage
                 └─ OR user cancels → FilePond DELETEs via revert()
```

## Related

- [FileLibraryController](./file-library-controller) — promotes temporary files to the permanent file library
- [MediaLibraryController](./media-library-controller) — promotes temporary files to the media library
- [File Storage with FilePond](/guide/generics/file-storage-with-filepond) — integration guide
