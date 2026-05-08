---
sidebarPos: 6
sidebarTitle: Overview
---

# Uploader Services

**Directory**: `src/Services/Uploader/`

The Uploader namespace provides **direct-to-cloud upload signing** for S3 and Azure Blob Storage. Rather than routing file bytes through the Laravel server, the browser receives a signed policy or SAS URL and uploads directly to the cloud provider.

## Classes

| Class | Description | Page |
|-------|-------------|------|
| [SignUploadListener](/system-reference/backend/services/uploader/sign-upload-listener) | Callback interface for upload signing results | [→](/system-reference/backend/services/uploader/sign-upload-listener) |
| [SignS3Upload](/system-reference/backend/services/uploader/sign-s3-upload) | Signs AWS S3 browser-direct upload policies (AWS Signature V4) | [→](/system-reference/backend/services/uploader/sign-s3-upload) |
| [SignAzureUpload](/system-reference/backend/services/uploader/sign-azure-upload) | Generates Azure Blob SAS URLs for browser-direct uploads | [→](/system-reference/backend/services/uploader/sign-azure-upload) |

## Flow

```
Browser                  Laravel Server                   Cloud
  |                           |                              |
  |-- POST /sign-upload ----→ |                              |
  |                     [SignS3Upload or SignAzureUpload]     |
  |                     validates & signs policy             |
  |← signed policy / SAS URL |                              |
  |                           |                              |
  |-- PUT file directly -----------------------------------→ |
```

The Laravel endpoint is lightweight: it validates the policy and returns a signature. The actual file transfer bypasses the server entirely.

## Disk Configuration

Both signers read credentials from the Laravel filesystem disk config:

```php
// config/filesystems.php
'disks' => [
    'libraries' => [
        'driver' => 's3',
        'bucket' => env('AWS_BUCKET'),
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
    ],
],
```

Pass the disk name (default `'libraries'`) as the `$disk` parameter to the signing methods.
