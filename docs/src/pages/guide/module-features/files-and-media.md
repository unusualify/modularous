---
sidebarPos: 2
---

# Files and Media

Files and Media (Images) follow the same triple pattern. Use **Files** for documents (PDF, DOC); use **Images** (Media) for images with cropping and transformations.

## Files

### Entity: HasFiles

```php
use Unusualify\Modularity\Entities\Traits\HasFiles;

class MyModel extends Model
{
    use HasFiles;
}
```

**Relationships**: `morphToMany(File::class, 'fileable')` with pivot `role`, `locale`.

**Methods**:
- `file($role, $locale = null)` — URL of first file for role/locale
- `filesList($role, $locale = null)` — array of URLs
- `fileObject($role, $locale = null)` — File model

### Repository: FilesTrait

Add to your repository:

```php
use Unusualify\Modularity\Repositories\Traits\FilesTrait;

class MyRepository extends Repository
{
    use FilesTrait;
}
```

**Columns**: Inputs with `type` containing `file` are registered as file columns (e.g. `documents`, `attachments`).

### Hydrate: FileHydrate

Route config:

```php
[
    'type' => 'file',
    'name' => 'documents',
    'translated' => false,
]
```

Output: `type` → `input-file`, rendered by `VInputFile`.

---

## Media (Images)

### Entity: HasImages

```php
use Unusualify\Modularity\Entities\Traits\HasImages;

class MyModel extends Model
{
    use HasImages;
}
```

**Relationships**: `morphToMany(Media::class, 'mediable')` with pivot `role`, `locale`. Supports crop params (`crop_x`, `crop_y`, `crop_w`, `crop_h`).

**Methods**:
- `medias()` — relationship
- `findMedia($role, $locale = null)` — first Media for role/locale
- `image($role, $locale = null)` — URL
- `imagesList($role, $locale = null)` — array of URLs

### Repository: ImagesTrait

Add to your repository. Handles `hydrateImagesTrait`, `afterSaveImagesTrait`, `getFormFieldsImagesTrait`.

### Hydrate: ImageHydrate

Route config:

```php
[
    'type' => 'image',
    'name' => 'images',
    'translated' => false,
]
```

Output: `type` → `input-image`, rendered by `VInputImage`.

---

## Filepond (Direct Upload)

Filepond is **one-to-many** direct binding (no file library). Use when you need simple file upload without Media/File library.

### Entity: HasFileponds

```php
use Unusualify\Modularity\Entities\Traits\HasFileponds;

class MyModel extends Model
{
    use HasFileponds;
}
```

**Relationships**: `morphMany(Filepond::class, 'filepondable')`.

### Repository: FilepondsTrait

### Hydrate: FilepondHydrate

Route config:

```php
[
    'type' => 'filepond',
    'name' => 'attachments',
    'max' => 5,
    'acceptedExtensions' => ['pdf', 'doc', 'docx'],
]
```

See [File Storage with Filepond](/guide/generics/file-storage-with-filepond) for full setup.
