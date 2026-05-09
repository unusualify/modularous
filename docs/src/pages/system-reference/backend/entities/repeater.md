---
sidebarPos: 19
sidebarTitle: Repeater
---

# Repeater

**File**: `src/Entities/Repeater.php`
**Namespace**: `Unusualify\Modularous\Entities`
**Extends**: `Model`

Eloquent model that persists the rows created by the `HasRepeaters` trait. Each row represents one item in a repeater field (e.g., one FAQ entry, one team member). Extends the Modularous `Model` base class and supports soft deletes.

## Database Table

Configurable via `modularousConfig('tables.repeaters', 'modularous_repeaters')`. Defaults to `modularous_repeaters`.

### Columns

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | UUID / auto-increment | No | Primary key (type follows `modularousIncrementsMethod()`) |
| `repeatable_type` | string (UUID morphs) | No | Class of the owning model |
| `repeatable_id` | UUID | No | ID of the owning model |
| `content` | JSON | No | All field values for this repeater item |
| `role` | string | Yes | The input `name` in the module config — identifies which repeater field this row belongs to |
| `locale` | string(6) | No | Locale code (e.g. `'en'`, `'de'`) — indexed |
| `created_at` / `updated_at` | timestamp | — | Standard timestamps |
| `deleted_at` | timestamp | Yes | Soft-delete column |

## Fillable Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `repeatable_id` | `int` | Parent model ID |
| `repeatable_type` | `string` | Parent model class |
| `content` | `array` | Repeater data (JSON cast) |
| `role` | `string` | Input field role identifier |
| `locale` | `string` | Locale for translated repeaters |

## Relationships

```php
// Owning model (any model using HasRepeaters)
public function repeatable(): MorphTo
```

No reverse eager-loading is defined on `Repeater` itself — access is always from the owning model via `$model->repeaters($role, $locale)`.

## `getTable()`

The table name is resolved at runtime:

```php
public function getTable(): string
{
    return modularousConfig('tables.repeaters', parent::getTable());
}
```

## How `role` and `locale` Work Together

A single model can have multiple repeater fields. `role` discriminates between them; `locale` discriminates between translations within the same field.

```
Page { id: 1 }
├── Repeater { repeatable_id: 1, role: 'faqs',        locale: 'en', content: { question: 'What?', answer: '...' } }
├── Repeater { repeatable_id: 1, role: 'faqs',        locale: 'en', content: { question: 'How?',  answer: '...' } }
├── Repeater { repeatable_id: 1, role: 'faqs',        locale: 'de', content: { question: 'Was?',  answer: '...' } }
└── Repeater { repeatable_id: 1, role: 'team_members', locale: 'en', content: { name: 'Alice', title: 'CTO' } }
```

### Querying by role + locale

The `HasRepeaters` trait's `repeaters()` method applies these filters:

```php
// All English FAQs on a page
$page->repeaters('faqs', 'en')->get();

// All repeaters regardless of role/locale
$page->repeaters()->get();
```

## `content` JSON Schema

The `content` column stores all field values for a single repeater item as a flat or nested object, depending on the input schema defined in the module config:

```json
{
    "question": "What is Modularous?",
    "answer": "A Laravel module framework.",
    "icon": null
}
```

For translated fields (`translated: true` in the schema), values are nested by locale:

```json
{
    "question": {
        "en": "What is Modularous?",
        "de": "Was ist Modularous?"
    }
}
```

## Difference from Block

| Aspect | Repeater | Block |
|--------|----------|-------|
| Nesting | Flat rows only — no parent/child | One level of parent/child via `parent_id` |
| Type system | No `type` — all rows under a role are the same shape | Each row has a `type` string mapping to a Blade view |
| Rendering | Via PHP/Inertia (frontend handles layout) | Via Blade views (`renderBlocks()`) |
| Media | Not built-in | `HasFiles`, `HasImages` on the Block model |
| Locale | Stored as column (`locale`) | Stored inside `content` JSON per field |
| Table | `modularous_repeaters` (configurable) | `twill_blocks` (configurable) |

## Related

- [HasRepeaters](/system-reference/backend/entity-traits/repeaters/has-repeaters) — entity trait and repository trait guide
- [Repeaters developer guide](/guide/module-features/repeaters) — input schema and runtime usage
- [Block](./block) — similar content-storage pattern for the block editor
