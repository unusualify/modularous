---
sidebarPos: 10
sidebarTitle: FileLibraryController
---

# FileLibraryController

**File**: `src/Http/Controllers/FileLibraryController.php`  
**Namespace**: `Unusualify\Modularity\Http\Controllers`  
**Extends**: `BaseController`  
**Implements**: `SignUploadListener`

Manages the file library — uploading, listing, tagging, and (optionally) cloud-signing files. Supports local filesystem storage, Amazon S3, and Azure Blob Storage.

## Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$moduleName` | `string` | `'File'` | — |
| `$routeName` | `string` | `'File'` | — |
| `$routePrefix` | `string` | `'file-library'` | — |
| `$perPage` | `int` | `40` | Files per page |
| `$endpointType` | `string` | — | `'local'` or cloud provider key |
| `$defaultFilters` | `array` | `['search', 'tag', 'unused']` | Active filter keys |
| `$defaultOrders` | `array` | `[['column' => 'id', 'direction' => 'desc']]` | Default sort |

## Methods

### `index($parentId = null): array`

Returns a paginated list of files with filtering by search term, tag, and unused status.

### `getIndexData(array $scopes): array`

Returns `['files', 'maxPage', 'total', 'tags']` — the raw data used by the index view.

### `store($parentId = null): JsonResponse`

Dispatches to `storeFile()` for local uploads or `storeReference()` for cloud references.

### `storeFile(Request $request): File`

Handles a local file upload:

1. Sanitises the filename (whitespace → dashes).
2. Generates a UUID-based storage path with an optional local prefix.
3. Stores via the configured disk.
4. Persists the `File` record.

### `storeReference(Request $request): File`

Records a reference to a file already stored in cloud storage (bypasses local upload).

### `singleUpdate(): JsonResponse`

Updates tags for a single file.

### `bulkUpdate(): JsonResponse`

Updates tags for multiple files in one request.

### `signS3Upload(Request $request, SignS3Upload $signer): mixed`

Signs an S3 pre-signed policy for direct browser-to-S3 uploads. Delegates to the `SignS3Upload` action class.

### `signAzureUpload(Request $request, SignAzureUpload $signer): mixed`

Returns an Azure Blob Storage SAS URL for direct browser-to-Azure uploads.

### `uploadIsSigned($signature, bool $isPublic = false): JsonResponse|Response`

Called by `SignUploadListener` after a successful signing — returns the signed URL to the frontend.

### `uploadIsNotValid(): JsonResponse`

Called by `SignUploadListener` on signing failure.

### `shouldReplaceFile($id): bool`

Returns `true` when a file with the given ID exists, indicating the upload is a replacement.

### `buildFile($item): array`

Formats a `File` model into the shape expected by the frontend:

```json
{
  "id": 1,
  "name": "document.pdf",
  "url": "https://...",
  "tags": [...],
  "mediableFormat": {...}
}
```

### `getRequestFilters(): array`

Extracts `search`, `tag`, and `unused` from the request.

## Cloud Storage

Cloud signing is only triggered when `$endpointType` is not `'local'`. The controller implements `SignUploadListener` to handle the async result from the signing action classes.

## Related

- [MediaLibraryController](./media-library-controller) — image-specific variant with dimensions and alt text
- [FilepondController](./filepond-controller) — temporary upload handler used before library storage
