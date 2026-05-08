---
sidebarPos: 1
sidebarTitle: HasFileponds
---

# HasFileponds

**Namespace**: `Unusualify\Modularity\Entities\Traits\HasFileponds`

Tracks Filepond temporary file uploads associated with a model. Uses `Core\ChangeRelationships` internally to flag which Filepond collections changed during a save cycle so downstream listeners (e.g., `FilepondManager`) can process or clean up uploads.

---

## Dependencies

Automatically uses `Core\ChangeRelationships` (mixed in via `use ChangeRelationships`).

---

## Relationship

```php
public function fileponds(): MorphMany  // → Filepond model
```

If `$filepondableClass` is set on the model, the morph source is proxied through that class.

---

## Properties

| Property | Type | Description |
|----------|------|-------------|
| `$deletedFileponds` | `Collection` | Filepond records that were removed in the current save cycle |
| `$newFileponds` | `Collection` | Filepond records added in the current save cycle |
| `$filepondableClass` | `string\|null` | Optional proxy class — use when the model's Filepond records are owned by a related class |

---

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `getFileponds` | `(): Filepond[]` | Returns all Filepond records for this model |
| `hasFilepond` | `(?string $role = null): bool` | Checks whether any (or a specific-role) Filepond exists |
| `addFilepondsAsChanged` | `(Collection $fileponds): void` | Merges records into `changedRelationships['fileponds']` |
| `setDeletedFilepondsAsChanged` | `(Collection $fileponds): void` | Replaces the deleted-fileponds tracking collection |
| `mergeDeletedFilepondsAsChanged` | `(Collection $fileponds): void` | Merges into the deleted-fileponds tracking collection |
| `setNewFilepondsAsChanged` | `(Collection $fileponds): void` | Sets the new-fileponds tracking collection |
| `hasDeletedFileponds` | `(): bool` | Returns `true` if any Filepond records were deleted this cycle |
| `hasNewFileponds` | `(): bool` | Returns `true` if any Filepond records were added this cycle |
| `getDeletedFileponds` | `(): Collection` | Returns the deleted-fileponds collection |
| `getNewFileponds` | `(): Collection` | Returns the new-fileponds collection |
| `getFilepondableClass` | `(): Model` | Returns the effective proxy model (or `$this`) |

---

## Usage

```php
use Unusualify\Modularity\Entities\Traits\HasFileponds;

class Article extends Model
{
    use HasFileponds;
}

// Access all Filepond records
$article->fileponds()->get();
$article->getFileponds();

// Check existence
$article->hasFilepond();              // any role
$article->hasFilepond('gallery');     // specific role

// Track changes (used internally by form submission pipeline)
$article->addFilepondsAsChanged($newPonds);
$article->hasDeletedFileponds();      // true if any were removed
$article->getDeletedFileponds();
```
