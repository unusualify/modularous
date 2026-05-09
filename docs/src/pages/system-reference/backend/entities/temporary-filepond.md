---
sidebarPos: 28
sidebarTitle: TemporaryFilepond
---

# TemporaryFilepond

**File**: `src/Entities/TemporaryFilepond.php`  
**Namespace**: `Unusualify\Modularous\Entities`  
**Extends**: `Illuminate\Database\Eloquent\Model`

Tracks temporary Filepond uploads before they are promoted to permanent [Filepond](./filepond) records on form submission. Automatically generates a unique folder name on creation.

## Fillable Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `file_name` | `string` | Original filename |
| `input_role` | `string` | Form input role this upload belongs to |
| `folder_name` | `string` | Unique folder name (auto-generated via `uniqid`) |

## Boot Behaviour

On `creating`, if `folder_name` is null, a unique folder name is generated using `uniqid('', true)`.

## Table

Resolved from `modularous.tables.filepond_temporaries`.

## Related

- [Filepond](./filepond) — permanent upload record
- [FilepondController](/system-reference/backend/http/controllers/filepond-controller) — upload/revert lifecycle
