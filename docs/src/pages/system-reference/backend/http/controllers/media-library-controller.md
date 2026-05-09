---
sidebarPos: 15
sidebarTitle: MediaLibraryController
---

# MediaLibraryController

**File**: `src/Http/Controllers/MediaLibraryController.php`  
**Namespace**: `Unusualify\Modularous\Http\Controllers`  
**Extends**: `BaseController`  
**Implements**: `SignUploadListener`

Manages the media (image) library. Similar to `FileLibraryController` but stores image dimensions, alt text, captions, and custom metadata fields alongside each uploaded file.

## Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$moduleName` | `string` | `'Media'` | — |
| `$routeName` | `string` | `'Media'` | — |
| `$perPage` | `int` | `40` | Images per page |
| `$endpointType` | `string` | — | `'local'` or cloud provider key |
| `$customFields` | `array` | `[]` | Extra metadata field keys |
| `$defaultFilters` | `array` | `['search', 'tag', 'unused']` | Active filter keys |
| `$defaultOrders` | `array` | `[['column' => 'id', 'direction' => 'desc']]` | Default sort |

## Methods

### `index($parentId = null): array`

Returns a paginated media list with tag counts and filter options.

### `getIndexData(array $scopes): array`

Returns `['items', 'maxPage', 'total', 'tags']`.

### `store($parentId = null): JsonResponse`

Dispatches to `storeFile()` for local uploads or `storeReference()` for cloud references.

### `storeFile(Request $request): Media`

Handles a local image upload:

1. Extracts image dimensions via `getimagesize()`.
2. Sanitises filename and generates a UUID storage path.
3. Stores the file on the configured disk.
4. Persists the `Media` record with `width` and `height`.

### `storeReference(Request $request): Media`

Records a reference to an image stored in cloud storage.

### `singleUpdate(): JsonResponse`

Updates one media item's `alt_text`, `caption`, `tags`, and custom fields.

### `bulkUpdate(): JsonResponse`

Bulk-updates `alt_text`, `caption`, and tags across multiple media items. Supports a `remove` mode that clears a field from all selected items.

### `bulkDelete(): JsonResponse`

Soft-deletes multiple media items.

### `signS3Upload(Request $request, SignS3Upload $signer): mixed`

Signs an S3 pre-signed upload policy.

### `signAzureUpload(Request $request, SignAzureUpload $signer): mixed`

Returns an Azure Blob SAS URL.

### `uploadIsSigned($signature, bool $isPublic = false): JsonResponse|Response`

Returns the signed URL to the frontend on success.

### `uploadIsNotValid(): JsonResponse`

Returns an error on signing failure.

### `shouldReplaceMedia($id): bool`

Returns `true` when the given ID matches an existing media record (replacement flow).

### `getExtraMetadatas(): Collection`

Extracts values for `$customFields` from the current request and returns them as a keyed collection.

## Custom Fields

Define additional metadata fields per deployment by setting `$customFields` on a subclass:

```php
protected array $customFields = ['photographer', 'license'];
```

These fields are extracted from the request in `getExtraMetadatas()` and persisted alongside the media record.

## Related

- [FileLibraryController](./file-library-controller) — non-image file variant
- [GlideController](./glide-controller) — serves transformed images from this library
- [FilepondController](./filepond-controller) — temporary upload handler
