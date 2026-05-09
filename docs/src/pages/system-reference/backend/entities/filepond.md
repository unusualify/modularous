---
sidebarPos: 11
sidebarTitle: Filepond
---

# Filepond

**File**: `src/Entities/Filepond.php`  
**Namespace**: `Unusualify\Modularous\Entities`  
**Extends**: `Model`

Represents a permanent Filepond file upload attached to a model via a polymorphic relationship. Dispatches notification events on create, update, and delete lifecycle hooks.

## Fillable Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `uuid` | `string` | UUID-based storage path |
| `file_name` | `string` | Original filename |
| `filepondable_id` | `int` | Parent model ID |
| `filepondable_type` | `string` | Parent model class |
| `role` | `string` | Upload role (e.g. `avatar`, `attachments`, `document`) |
| `locale` | `string` | Locale for the upload |

## Boot Events

| Event | Action |
|-------|--------|
| `created` | Dispatches `FilepondCreated` |
| `updated` | Dispatches `FilepondUpdated` |
| `deleted` | Dispatches `FilepondDeleted` |

## Relationships

### `filepondable(): MorphTo`

The parent model this file is attached to.

## Methods

### `mediableFormat(): array`

Returns a frontend-friendly format with `uuid`, `file_name`, `source` (preview URL), `created_at`, and `file` info from the `FilepondManager`.

## Related

- [TemporaryFilepond](./temporary-filepond) — temporary upload before form submission
- [HasFileponds](/system-reference/backend/entity-traits/media/has-fileponds) — attaches Filepond uploads to models
- [FilepondController](/system-reference/backend/http/controllers/filepond-controller) — handles upload/revert/preview
