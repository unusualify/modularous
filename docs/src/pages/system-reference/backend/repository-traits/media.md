---
sidebarPos: 3
sidebarTitle: Media Traits
---

# Media Repository Traits

These traits handle persistence of file and image attachments from forms into pivot tables. They pair with the corresponding [Entity Traits](../entity-traits/media/overview) (`HasFiles`, `HasImages`, `HasFileponds`) which define the Eloquent relationships.

---

## FilepondsTrait

**Namespace**: `Unusualify\Modularity\Repositories\Traits\FilepondsTrait`

Persists Filepond temporary uploads to permanent storage via the `Filepond` facade after a model is saved. Supports nested repeater files, locale-separated uploads, and associative (translated) file arrays.

### Lifecycle Hooks

| Hook | Description |
|------|-------------|
| `setColumnsFilepondsTrait` | Scans form inputs for `type: filepond` and registers their names |
| `afterSaveFilepondsTrait` | Iterates registered columns and delegates to `Filepond::saveFile()` |
| `getFormFieldsFilepondsTrait` | Loads existing Filepond records grouped by role/locale into form fields |

### Column Detection

Inputs whose `type` matches the pattern `/filepond/` are automatically claimed by this trait.

### After-Save Flow

```
afterSaveFilepondsTrait($object, $fields)
  └─ for each filepond column:
       ├─ nested repeater pattern (*.*)? → iterate indices, save per-index
       ├─ associative (locale keys)?    → save per-locale via Filepond::saveFile()
       └─ flat array?                   → save directly via Filepond::saveFile()
```

### Form Field Hydration

When editing an existing record, the trait loads `$object->fileponds` grouped by `role`, then:

- **Translated inputs** — groups by locale and maps each to `mediableFormat()`.
- **Non-translated inputs** — uses the default locale (or first available) and maps to `mediableFormat()`.
- **Missing roles** — initializes with empty arrays (locale-keyed if translated).

### Usage

```php
// In your repository — just use the trait
use Unusualify\Modularity\Repositories\Traits\FilepondsTrait;

class ArticleRepository extends Repository
{
    use FilepondsTrait;
}

// Form inputs detected automatically:
// ['type' => 'filepond', 'name' => 'documents']
// ['type' => 'filepond', 'name' => 'gallery', 'translated' => true]
```

---

## FilesTrait

**Namespace**: `Unusualify\Modularity\Repositories\Traits\FilesTrait`

Syncs `File` model attachments through the `fileables` pivot table. Handles locale-aware file assignment, pivot record creation/update, and in-memory hydration for preview.

### Lifecycle Hooks

| Hook | Description |
|------|-------------|
| `setColumnsFilesTrait` | Registers inputs matching `type: file` (exact word boundary) |
| `hydrateFilesTrait` | Sets the `files` relation in-memory without persisting — used for preview/validation |
| `afterSaveFilesTrait` | Attaches new files or updates existing pivot records via `$object->files()` |
| `getFormFieldsFilesTrait` | Loads existing files grouped by role/locale into form field arrays |

### Column Detection

Inputs matching the pattern `/\bfile\b/` (word boundary) are registered.

### Hydration (Preview)

```php
hydrateFilesTrait($object, $fields)
```

Builds a `Collection` of `File` models with in-memory pivots (`role`, `locale`, `file_id`) and sets it as the `files` relation. This lets downstream code (presenters, serializers) access files before the save is committed.

### After-Save Flow

For each file in the resolved file list:
- If the file has an existing pivot `id` → `updateExistingPivot()`.
- Otherwise → `attach()` with role and locale metadata.

### Form Field Hydration

Groups `$object->files` by `pivot.role`, then:

- **Translated inputs** — further groups by `pivot.locale` and maps each to `mediableFormat()`.
- **Non-translated inputs** — selects the default locale (fallback to `app.fallback_locale`) and maps to `mediableFormat()`.
- **Missing roles** — initializes empty `Collection` (locale-keyed if translated).

### Usage

```php
use Unusualify\Modularity\Repositories\Traits\FilesTrait;

class DocumentRepository extends Repository
{
    use FilesTrait;
}

// Detected from form schema:
// ['type' => 'file', 'name' => 'attachment']
// ['type' => 'file', 'name' => 'contract', 'translated' => true]
```

---

## ImagesTrait

**Namespace**: `Unusualify\Modularity\Repositories\Traits\ImagesTrait`

Syncs `Media` model attachments through the `mediables` pivot table. Structurally similar to `FilesTrait` but handles image-specific metadata (crop settings, image metadatas).

### Lifecycle Hooks

| Hook | Description |
|------|-------------|
| `setColumnsImagesTrait` | Registers inputs matching `type: image` |
| `hydrateImagesTrait` | Sets the `medias` relation in-memory for preview |
| `afterSaveImagesTrait` | Attaches or updates media pivot records |
| `getFormFieldsImagesTrait` | Loads existing media grouped by role/locale into form fields |

### Column Detection

Inputs matching the pattern `/image/` are registered.

### After-Save Flow

For each media item in the resolved media list:
- If the media has an existing pivot `id` → `updateExistingPivot()`.
- Otherwise → `attach()` with `media_id`, `role`, `metadatas`, `crop`, and `locale`.

### Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `pushImage` | `($object, $images, $imagesData, $role, $locale, $index): Collection` | Appends image pivot data to the collection, resolving existing pivot IDs |
| `getCrops` | `(string $role): array` | Returns crop configuration from `$model->mediasParams[$role]` |

### Form Field Hydration

Same grouping strategy as `FilesTrait`:

- **Translated** — grouped by `pivot.locale`, each mapped to `mediableFormat()`.
- **Non-translated** — default/fallback locale, mapped to `mediableFormat()`.
- **Missing roles** — empty `Collection` (locale-keyed if translated).

### Usage

```php
use Unusualify\Modularity\Repositories\Traits\ImagesTrait;

class ProductRepository extends Repository
{
    use ImagesTrait;
}

// Detected from form schema:
// ['type' => 'image', 'name' => 'cover']
// ['type' => 'image', 'name' => 'gallery', 'translated' => true]

// Get crop config
$repo->getCrops('cover'); // ['default' => [...], 'thumbnail' => [...]]
```
