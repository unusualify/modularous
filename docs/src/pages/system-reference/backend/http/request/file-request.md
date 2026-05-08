---
sidebarPos: 4
sidebarTitle: FileRequest
---

# FileRequest

**File**: `src/Http/Requests/FileRequest.php`
**Namespace**: `Unusualify\Modularity\Http\Requests`
**Extends**: [`Request`](./request)

Validates the payload posted to the file-library upload endpoint. The required fields depend on which backend is configured via `modularity.file_library.endpoint_type`.

## Endpoint-specific rules

`rules()` reads `modularityConfig('file_library.endpoint_type')` and returns one of three rule sets:

| `endpoint_type` | Required fields |
|-----------------|-----------------|
| `local` | `qqfilename`, `qqfile`, `qqtotalfilesize` |
| `azure` | `blob`, `name` |
| `s3` (default) | `key`, `name` |

The `s3` branch is used as the fallback for any unknown value.

## Notes

- Despite extending the model-aware [`Request`](./request) base, `FileRequest` overrides `rules()` completely — no translation expansion or `unique_table` hydration runs for this endpoint.
- The per-backend field names mirror the client libraries they integrate with: FineUploader (`qq*` fields) for `local`, the Azure Blob SDK for `azure`, and S3 presigned uploads for `s3`.

## Related

- [`FileLibraryController`](/system-reference/backend/http/controllers/file-library-controller) — consumes this request
- [`FilepondController`](/system-reference/backend/http/controllers/filepond-controller) — related uploader endpoint
- [Services · FileLibrary](/system-reference/backend/services/file-library/overview)
