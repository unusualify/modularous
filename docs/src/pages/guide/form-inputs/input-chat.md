---
sidebarPos: 5
sidebarTitle: Chat
---

# Chat

The `chat` input type renders `VInputChat`, a full messaging thread embedded directly in a form. It supports paginated message history, file attachments via Filepond, message pinning, starring, and an expandable "Show All" dialog. The widget does **not** submit its value as part of the form (`noSubmit: true`).

## Hydrate

**Class:** `ChatHydrate`
**Config type:** `chat`
**Output type:** `input-chat` → `VInputChat`

The hydrate:
- Sets `name` to `_chat_id` and forces `noSubmit: true` and `rules: ''`
- Auto-wires all chat endpoints (`index`, `store`, `show`, `update`, `destroy`, `attachments`, `pinnedMessage`) via named routes (`admin.chatable.*`)
- Embeds a Filepond attachment config (defaults: PDF/doc/docx/pages, max 3 files)
- Sets `default` to `-1` (no active chat until a record is loaded)
- Sets `creatable: 'hidden'` so the Create form hides this field

## Usage

### Minimal

```php
[
    'type' => 'chat',
]
```

### Custom height and attachment types

```php
[
    'type'               => 'chat',
    'label'              => 'Support Messages',
    'height'             => '60vh',
    'bodyHeight'         => '44vh',
    'acceptedExtensions' => ['pdf', 'jpg', 'png'],
    'max-attachments'    => 5,
]
```

### Custom card styling

```php
[
    'type'         => 'chat',
    'variant'      => 'elevated',
    'color'        => 'blue-lighten-5',
    'elevation'    => 2,
    'inputVariant' => 'outlined',
]
```

## Schema Defaults

| Key | Default | Description |
|-----|---------|-------------|
| `default` | `-1` | No chat until record is loaded |
| `height` | `'40vh'` | Total widget height |
| `bodyHeight` | `'26vh'` | Scrollable message area height |
| `variant` | `'outlined'` | Card variant |
| `elevation` | `0` | Card elevation |
| `color` | `'grey-lighten-2'` | Card background colour |
| `inputVariant` | `'outlined'` | Text input variant |
| `noSubmit` | `true` | Not included in form submission |
| `creatable` | `'hidden'` | Hidden on the Create form |

## See Also

- [Filepond](/guide/form-inputs/input-filepond) — Used internally for message attachments
- [Hydrates reference](/system-reference/hydrates) — Resolution table and schema contract
