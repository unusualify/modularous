---
sidebarPos: 1
sidebarTitle: SignUploadListener
---

# SignUploadListener

**File**: `src/Services/Uploader/SignUploadListener.php`

`SignUploadListener` is a callback interface implemented by the controller (or any class) that calls the upload signing services. It decouples the signing logic from the HTTP response format.

## Interface Definition

```php
interface SignUploadListener
{
    public function uploadIsSigned($signature, $isJsonResponse = true);

    public function uploadIsNotValid();
}
```

## Methods

| Method | Parameters | Called when |
|--------|-----------|-------------|
| `uploadIsSigned` | `$signature` — signed policy array (S3) or SAS URL string (Azure); `$isJsonResponse` — whether to return JSON (default `true`) | Signing succeeded |
| `uploadIsNotValid` | — | Policy is invalid or signing failed |

## Implementing in a Controller

```php
use Unusualify\Modularous\Services\Uploader\SignUploadListener;

class FileUploadController extends Controller implements SignUploadListener
{
    public function sign(Request $request, SignS3Upload $signer)
    {
        return $signer->fromPolicy($request->input('policy'), $this);
    }

    public function uploadIsSigned($signature, $isJsonResponse = true)
    {
        if ($isJsonResponse) {
            return response()->json($signature);
        }
        return $signature;
    }

    public function uploadIsNotValid()
    {
        return response()->json(['error' => 'Invalid upload policy'], 422);
    }
}
```
