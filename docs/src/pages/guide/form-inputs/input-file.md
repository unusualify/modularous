---
sidebarPos: 15
sidebarTitle: File
---

# File

The `file` input type renders `VInputFile`, a drag-and-drop file list backed by the Modularous media library. It supports multiple files, drag-to-reorder (via `vuedraggable`), and displays each file as a row with a delete button. Unlike [Filepond](/guide/form-inputs/input-filepond), this component uses the internal media library rather than the Filepond server protocol.

## Hydrate

**Class:** `FileHydrate`
**Config type:** `file`
**Output type:** `input-file` → `VInputFile`

The hydrate sets `type` to `input-file` and defaults `label` to the translated string `"Files"`.

## Usage

### Basic

```php
[
    'type'  => 'file',
    'name'  => 'documents',
    'label' => 'Documents',
]
```

### Translated files

```php
[
    'type'       => 'file',
    'name'       => 'files',
    'translated' => true,
]
```

### Limit the number of files

Pass `max` to cap how many files can be attached.

```php
[
    'type' => 'file',
    'name' => 'attachments',
    'max'  => 3,
]
```

## Schema Defaults

| Key | Default | Description |
|-----|---------|-------------|
| `name` | `'files'` | Form field name |
| `translated` | `false` | Whether files are locale-specific |
| `default` | `[]` | Empty file list |
| `label` | `'Files'` | Auto-translated label |

## See Also

- [Filepond](/guide/form-inputs/input-filepond) — Alternative upload using the Filepond protocol
- [File Storage with Filepond](/guide/generics/file-storage-with-filepond) — Setup guide
- [Hydrates reference](/system-reference/hydrates) — Resolution table and schema contract
