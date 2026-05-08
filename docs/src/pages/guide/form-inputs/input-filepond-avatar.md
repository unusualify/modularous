---
sidebarPos: 16
sidebarTitle: Filepond Avatar
---

# Filepond Avatar <Badge type="tip" text="^0.9.2" />

The `filepond-avatar` input type renders `VInputFilepondAvatar`, a specialised variant of the Filepond upload component for avatar / profile photos. It limits uploads to a maximum of 2 files, auto-wires all Filepond server endpoints (process, revert, preview), and hides the Filepond credits banner.

## Hydrate

**Class:** `FilepondAvatarHydrate`
**Config type:** `filepond-avatar`
**Output type:** `input-filepond-avatar` → `VInputFilepondAvatar`

The hydrate:
- Sets `max` to `2` (hardcoded — avatars are capped at two files)
- Auto-resolves the three Filepond server endpoints from named routes (`filepond.process`, `filepond.revert`, `filepond.preview`)
- Sets `credits` to `false` to suppress the "powered by Filepond" banner
- Passes through `acceptedExtensions` when provided

## Usage

```php
[
    'type'  => 'filepond-avatar',
    'name'  => 'avatar',
    'label' => 'Profile Photo',
]
```

### Restrict accepted file types

```php
[
    'type'               => 'filepond-avatar',
    'name'               => 'avatar',
    'label'              => 'Profile Photo',
    'acceptedExtensions' => ['jpg', 'jpeg', 'png', 'webp'],
]
```

## Schema Defaults

| Key | Default | Description |
|-----|---------|-------------|
| `max` | `2` | Maximum number of files (hardcoded by hydrate) |
| `credits` | `false` | Hide the Filepond credits banner |
| `process` | route(`filepond.process`) | Server endpoint for uploads |
| `revert` | route(`filepond.revert`) | Server endpoint to cancel/revert an upload |
| `load` | route(`filepond.preview`) | Server endpoint to load existing files |

## See Also

- [Filepond](/guide/form-inputs/input-filepond) — General-purpose file upload with Filepond
- [File Storage with Filepond](/guide/generics/file-storage-with-filepond) — Setup and configuration
- [Hydrates reference](/system-reference/hydrates) — Resolution table and schema contract
