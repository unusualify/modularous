---
sidebarPos: 4
sidebarTitle: FilesUploaderConfig
---

# FilesUploaderConfig

**Class**: `Unusualify\Modularous\Http\ViewComposers\FilesUploaderConfig`  
**Source**: `src/Http/ViewComposers/FilesUploaderConfig.php`

Builds and injects the file-library upload configuration object into every view. The frontend file uploader (Filepond or equivalent) reads `$filesUploaderConfig` to know where and how to upload files.

## Injected Variable

| Variable | Type | Description |
|----------|------|-------------|
| `filesUploaderConfig` | `array` | Complete upload configuration for the file library |

## Config Object Shape

| Key | Source | Description |
|-----|--------|-------------|
| `endpointType` | `file_library.endpoint_type` | Storage backend: `local`, `s3`, or `azure` |
| `endpoint` | Resolved by `endpointType` | The URL the uploader POSTs files to |
| `successEndpoint` | `file-library.file.store` route | Always the local store route — used for the post-upload success callback |
| `signatureEndpoint` | `file-library.sign-s3-upload` or `sign-azure-upload` route | Pre-signed URL endpoint; `null` for `local` |
| `endpointBucket` | `filesystems.disks.{disk}.bucket` | S3/Azure bucket name; `'none'` when absent |
| `endpointRegion` | `filesystems.disks.{disk}.region` | S3 region; `'none'` when absent |
| `endpointRoot` | `filesystems.disks.{disk}.root` | Root path prefix; empty string for `local` |
| `accessKey` | `filesystems.disks.{disk}.key` | Storage access key; `'none'` when absent |
| `csrfToken` | Session token | Laravel CSRF token for request validation |
| `acl` | `file_library.acl` | S3/Azure ACL policy (e.g. `public-read`) |
| `filesizeLimit` | `file_library.filesize_limit` | Maximum upload size in bytes |
| `allowedExtensions` | `file_library.allowed_extensions` | Array of permitted file extensions |

## Endpoint Resolution by Type

| `endpointType` | `endpoint` | `signatureEndpoint` |
|----------------|-----------|---------------------|
| `local` | `file-library.file.store` route URL | `null` |
| `s3` | `s3Endpoint($libraryDisk)` helper | `file-library.sign-s3-upload` route URL |
| `azure` | `azureEndpoint($libraryDisk)` helper | `file-library.sign-azure-upload` route URL |

## Configuration

```php
// config/modularous.php
'file_library' => [
    'disk'               => 'local',
    'endpoint_type'      => 'local',    // 'local' | 's3' | 'azure'
    'allowed_extensions' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
    'acl'                => 'private',
    'filesize_limit'     => 10485760,   // 10 MB in bytes
],
```
