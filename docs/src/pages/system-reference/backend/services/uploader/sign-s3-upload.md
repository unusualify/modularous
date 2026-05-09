---
sidebarPos: 2
sidebarTitle: SignS3Upload
---

# SignS3Upload

**File**: `src/Services/Uploader/SignS3Upload.php`

`SignS3Upload` validates and signs an AWS S3 browser-direct upload policy using **AWS Signature Version 4**. The browser constructs a policy document and POSTs it to the Laravel endpoint; this service verifies the bucket and size conditions, then returns a Base64-encoded policy and HMAC-SHA256 signature.

## Method

```php
public function fromPolicy(string $policy, SignUploadListener $listener, string $disk = 'libraries'): mixed
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$policy` | `string` | JSON policy document from the browser |
| `$listener` | `SignUploadListener` | Callback interface for success/failure |
| `$disk` | `string` | Laravel filesystem disk name (default `'libraries'`) |

Reads `bucket` and `secret` from `filesystems.disks.{$disk}`.

## Signing Flow

1. Decodes the JSON policy document.
2. Validates that `bucket` matches the configured disk bucket and `content-length-range` matches the expected max size.
3. Base64-encodes the policy JSON → `policy`.
4. Derives the AWS V4 signing key from the credential condition (`x-amz-credential`): `HMAC(HMAC(HMAC(HMAC('AWS4'+secret, date), region), 's3'), 'aws4_request')`.
5. Signs the encoded policy with the derived key → `signature`.
6. Returns `['policy' => $encodedPolicy, 'signature' => $hexSignature]` via `$listener->uploadIsSigned()`.

## Response Structure

```json
{
  "policy": "eyJleHBpcmF0aW9uIjoiMjAyNi0wMS0wMVQwMDowMDowMFoiLCJjb25kaXRpb25zIjpbXX0=",
  "signature": "a3f9e2b1c4d5..."
}
```

## Example Controller

```php
use Unusualify\Modularous\Services\Uploader\SignS3Upload;
use Unusualify\Modularous\Services\Uploader\SignUploadListener;

class MediaController extends Controller implements SignUploadListener
{
    public function signS3(Request $request, SignS3Upload $signer)
    {
        return $signer->fromPolicy($request->input('policy'), $this);
    }

    public function uploadIsSigned($signature, $isJsonResponse = true)
    {
        return response()->json($signature);
    }

    public function uploadIsNotValid()
    {
        return response()->json(['error' => 'Invalid policy'], 422);
    }
}
```

## Notes

- The `expectedMaxSize` check in `isValid()` compares against `null` by default; override the method in a subclass to enforce a specific file size limit.
- Requires the disk's `secret` to be present in the filesystem disk config — not the global `AWS_SECRET_ACCESS_KEY` env variable unless the disk maps to it.
