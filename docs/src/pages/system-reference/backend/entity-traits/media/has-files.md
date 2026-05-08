---
sidebarPos: 2
sidebarTitle: HasFiles
---

# HasFiles

**Namespace**: `Unusualify\Modularity\Entities\Traits\HasFiles`

Attaches files from the `File` model via a `MorphToMany` through the `modularity_fileables` pivot table. Locale-aware: each file attachment stores a `role` and `locale` pivot column, so a model can have different files per language.

---

## Relationship

```php
public function files(): MorphToMany
```

Pivot columns: `role`, `locale`. Ordered by pivot `id` ascending.

---

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `file` | `(string $role, ?string $locale = null, ?File $file = null): ?string` | Returns the public URL for the file in the given role (and locale); falls back to `fallback_locale` when `translatable.use_property_fallback` is `true` |
| `filesList` | `(string $role, ?string $locale = null): array` | Returns an array of public URLs for all files in a role |
| `fileObject` | `(string $role, ?string $locale = null): ?File` | Returns the raw `File` Eloquent model |

---

## Configuration

| Config key | Default | Description |
|------------|---------|-------------|
| `modularity.tables.fileables` | — | Pivot table name for file attachments |
| `translatable.use_property_fallback` | `false` | Whether to fall back to `fallback_locale` when no file exists for the requested locale |
| `translatable.fallback_locale` | — | Locale used when the primary locale file is missing |

---

## Usage

```php
use Unusualify\Modularity\Entities\Traits\HasFiles;

class Document extends Model
{
    use HasFiles;
}

// URL for the current locale
$document->file('attachment');

// URL for a specific locale
$document->file('brochure', 'fr');

// All files for a role
$document->filesList('downloads');
$document->filesList('downloads', 'de');

// Raw File model
$document->fileObject('contract');
```
