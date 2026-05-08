---
sidebarPos: 14
sidebarTitle: FilepondManager
---

# FilepondManager

**File**: `src/Services/FilepondManager.php`  
**Facade**: `Unusualify\Modularity\Facades\Filepond`

Manages the full lifecycle of [FilePond](https://pqina.nl/filepond/) file uploads: accepting temporary uploads, serving previews, persisting files to permanent storage when a model is saved, and cleaning up orphaned or expired temporary files.

## Storage Layout

| Path | Purpose |
|------|---------|
| `public/fileponds/tmp/{uuid}/` | Temporary location — files land here on upload |
| `public/fileponds/{uuid}/` | Permanent location — files are moved here on model save |

Each uploaded file is tracked in the `temporary_fileponts` table via a `TemporaryFilepond` model, using a UUID (`folder_name`) as the unique identifier. On persist, a `Filepond` record is created and the temp record is deleted.

## Upload Flow

```
1. User selects file in VInputFilepond
2. Frontend POSTs to filepond upload route
3. createTemporaryFilepond() stores file in tmp/ and returns UUID
4. UUID is stored in form state
5. On form submit, saveFile() is called with submitted UUIDs
6. New UUIDs → persistFile() moves file from tmp/ to permanent path
7. Removed UUIDs → deleteFile() removes file and DB record
```

## Key Methods

### Upload & Temp Management

| Method | Description |
|--------|-------------|
| `createTemporaryFilepond(Request $request)` | Store an uploaded file in the temp path; returns the UUID string |
| `deleteTemporaryFilepond(Request $request)` | Delete a temp file by UUID (called when user removes a file before saving) |
| `clearTemporaryFiles($days = 7)` | Purge all temp uploads older than `$days` days (used by scheduler) |

### Persistence

| Method | Description |
|--------|-------------|
| `persistFile(TemporaryFilepond $temp, Model $model, $role, $locale)` | Move a temp file to permanent storage and create a `Filepond` DB record |
| `saveFile($object, $files, $role, $locale)` | Reconcile submitted UUID list against existing records — persists new files, deletes removed ones |
| `createFilepond($object, $temp, $role, $locale)` | Create a `Filepond` record after a file has been moved |
| `deleteFile($folder)` | Delete a permanent file from storage and its `Filepond` DB record |

### File Access

| Method | Description |
|--------|-------------|
| `previewFile($folder)` | Stream a file for inline preview — returns image response or file response depending on mime type |
| `getFileInfo($uuid)` | Return `['size', 'type', 'name']` for a UUID |
| `getStoragePath($uuid)` | Resolve the full storage path for a UUID (checks permanent first, then temp) |
| `getStorageFile($uuid)` | Return the first file path within the UUID folder |
| `getEncodedFile($folder)` | Return the file as a base64-encoded string |
| `clearFolders()` | Remove orphaned permanent folders not referenced by any `Filepond` record |

## Session Tracking

When a file is temporarily uploaded, its UUID is stored in the PHP session under `_filepond.{input_role}`. This allows the server to associate the temp file with the form field if the form is submitted without a full page reload.

```php
// Session key pattern
"_filepond.{input_role}" => "uuid-folder-name"
```

## Scheduler Integration

`FilepondsScheduler` calls `clearTemporaryFiles()` on a schedule to prevent the temp directory from accumulating stale uploads. Register it in your `Console/Kernel.php`:

```php
$schedule->call(function () {
    app(\Unusualify\Modularity\Services\FilepondManager::class)->clearTemporaryFiles(7);
})->daily();
```

## Facade Usage

```php
use Unusualify\Modularity\Facades\Filepond;

// In a controller upload endpoint
public function upload(Request $request)
{
    return Filepond::createTemporaryFilepond($request);
}

// In a controller delete endpoint
public function delete(Request $request)
{
    Filepond::deleteTemporaryFilepond($request);
}

// In a repository after model save
Filepond::saveFile($model, $request->input('documents'), 'documents', $locale);
```

## See Also

- [File Storage with FilePond](/guide/generics/file-storage-with-filepond) — guide-level walkthrough
- [VInputFilepond](/guide/form-inputs/input-filepond) — the Vue component
