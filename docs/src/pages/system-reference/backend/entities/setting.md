---
sidebarPos: 21
sidebarTitle: Setting
---

# Setting

**File**: `src/Entities/Setting.php`  
**Namespace**: `Unusualify\Modularity\Entities`  
**Extends**: `Model`  
**Traits**: `HasImages`, `HasTranslation`

Key-value settings model with translation support and image attachments. Settings are scoped by `section` and use a `key`/`value` structure where the value is translatable.

## Fillable Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `key` | `string` | Setting key |
| `section` | `string` | Setting section/group |

## Translated Attributes

| Attribute | Description |
|-----------|-------------|
| `value` | The setting value (per-locale) |
| `locale` | Active locale |
| `active` | Whether the translation is active |

## Configuration

- `$useTranslationFallback` is enabled — missing translations fall back to the default locale.
- Translation model: `Unusualify\Modularity\Entities\Translations\SettingTranslation`

## Table

Resolved from `modularity.settings_table`, defaults to `twill_settings`.

## Related

- [HasImages](/system-reference/backend/entity-traits/media/has-images) — settings can have associated images
- [HasTranslation](/system-reference/backend/entity-traits/translation/has-translation) — translated values
