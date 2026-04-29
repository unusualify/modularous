---
sidebarPos: 3
sidebarTitle: SignAzureUpload
---

# SignAzureUpload

**File**: `src/Services/Uploader/SignAzureUpload.php`  
**Requires**: `microsoft/azure-storage-blob`

`SignAzureUpload` generates an Azure Blob Storage **Shared Access Signature (SAS) URL** that authorises the browser to PUT or DELETE a blob directly, without routing file bytes through the Laravel server.

## Method

```php
public function getSasUrl(Request $request, SignUploadListener $listener, string $disk = 'libraries'): mixed
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$request` | `Request` | Must contain `bloburi` (full blob URL) and `_method` (`PUT` or `DELETE`) |
| `$listener` | `SignUploadListener` | Callback for success/failure |
| `$disk` | `string` | Laravel filesystem disk name (default `'libraries'`) |

## Request Inputs

| Input | Value |
|-------|-------|
| `bloburi` | Full Azure blob URL, e.g. `https://account.blob.core.windows.net/container/path/file.pdf` |
| `_method` | `PUT` (upload) or `DELETE` (remove) |

## Signing Flow

1. Reads `bloburi` and `_method` from the request.
2. Maps `PUT` → permission `'w'` (write), `DELETE` → permission `'d'` (delete).
3. Builds a `BlobSharedAccessSignatureHelper` using `name` and `key` from the disk config.
4. Sets expiry to `now + 15 minutes` (UTC).
5. Constructs the blob path by stripping the Azure endpoint prefix from `bloburi`.
6. Appends the SAS token to `bloburi` and returns it via `$listener->uploadIsSigned($sasUrl, false)`.

## Disk Configuration

```php
// config/filesystems.php
'disks' => [
    'libraries' => [
        'driver'    => 'azure',
        'name'      => env('AZURE_STORAGE_ACCOUNT'),
        'key'       => env('AZURE_STORAGE_KEY'),
        'container' => env('AZURE_STORAGE_CONTAINER'),
    ],
],
```

## Example Controller

```php
use Unusualify\Modularity\Services\Uploader\SignAzureUpload;
use Unusualify\Modularity\Services\Uploader\SignUploadListener;

class MediaController extends Controller implements SignUploadListener
{
    public function signAzure(Request $request, SignAzureUpload $signer)
    {
        return $signer->getSasUrl($request, $this);
    }

    public function uploadIsSigned($signature, $isJsonResponse = true)
    {
        // $signature is the full SAS URL string
        return response()->json(['sas_url' => $signature]);
    }

    public function uploadIsNotValid()
    {
        return response()->json(['error' => 'Could not generate SAS URL'], 422);
    }
}
```

## Notes

- SAS tokens expire after 15 minutes. Generate them as close to the upload as possible.
- Any exception during SAS generation (wrong credentials, network issues) is caught and routed to `uploadIsNotValid()`.
- `$isJsonResponse` is passed as `false` because the return value is already a complete URL string, not a structured object.
