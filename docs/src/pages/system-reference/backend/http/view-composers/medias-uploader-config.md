---
sidebarPos: 6
sidebarTitle: MediasUploaderConfig
---

# MediasUploaderConfig

**Class**: `Unusualify\Modularity\Http\ViewComposers\MediasUploaderConfig`  
**Source**: `src/Http/ViewComposers/MediasUploaderConfig.php`

Builds and injects the media-library upload configuration object into every view. The frontend image/media uploader reads `$mediasUploaderConfig` to know where and how to upload media files. Mirrors [`FilesUploaderConfig`](./files-uploader-config) but targets the media library instead of the file library.

## Injected Variable

| Variable | Type | Description |
|----------|------|-------------|
| `mediasUploaderConfig` | `array` | Complete upload configuration for the media library |

## Config Object Shape

| Key | Source | Description |
|-----|--------|-------------|
| `endpointType` | `media_library.endpoint_type` | Storage backend: `local`, `s3`, or `azure` |
| `endpoint` | Resolved by `endpointType` | The URL the uploader POSTs media files to |
| `successEndpoint` | `media-library.media.store` route | Always the local store route — used for the post-upload success callback |
| `signatureEndpoint` | `media-library.sign-s3-upload` or `sign-azure-upload` route | Pre-signed URL endpoint; `null` for `local` |
| `endpointBucket` | `filesystems.disks.{disk}.bucket` | S3/Azure bucket name; `'none'` when absent |
| `endpointRegion` | `filesystems.disks.{disk}.region` | S3 region; `'none'` when absent |
| `endpointRoot` | `filesystems.disks.{disk}.root` | Root path prefix; empty string for `local` |
| `accessKey` | `filesystems.disks.{disk}.key` | Storage access key; `'none'` when absent |
| `csrfToken` | Session token | Laravel CSRF token for request validation |
| `acl` | `media_library.acl` | S3/Azure ACL policy (e.g. `public-read`) |
| `filesizeLimit` | `media_library.filesize_limit` | Maximum upload size in bytes |
| `allowedExtensions` | `media_library.allowed_extensions` | Array of permitted media extensions |

## Endpoint Resolution by Type

| `endpointType` | `endpoint` | `signatureEndpoint` |
|----------------|-----------|---------------------|
| `local` | `media-library.media.store` route URL | `null` |
| `s3` | `s3Endpoint($libraryDisk)` helper | `media-library.sign-s3-upload` route URL |
| `azure` | `azureEndpoint($libraryDisk)` helper | `media-library.sign-azure-upload` route URL |

## Difference from FilesUploaderConfig

`MediasUploaderConfig` reads from `media_library.*` config keys and targets the `media-library.*` admin routes. `FilesUploaderConfig` reads from `file_library.*` and targets `file-library.*` routes. Both produce the same shape of configuration object.

## Configuration

```php
// config/modularity.php
'media_library' => [
    'disk'               => 's3',
    'endpoint_type'      => 's3',       // 'local' | 's3' | 'azure'
    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
    'acl'                => 'public-read',
    'filesize_limit'     => 52428800,   // 50 MB in bytes
],
```
