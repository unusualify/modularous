---
sidebarPos: 4
sidebarTitle: Block
---

# Block

**File**: `src/Entities/Block.php`
**Namespace**: `Unusualify\Modularity\Entities`
**Extends**: `Illuminate\Database\Eloquent\Model`
**Traits**: `HasFiles`, `HasImages`, `HasPresenter`, `HasRelated`

Eloquent model that backs the block content system. Extends Laravel's base `Model` directly (not the Modularous `Model`) and uses no timestamps. Blocks are attached to a parent model via a morph relation and can be nested (parent/child). Each block stores its content as a JSON array and has a configurable type and editor name.

## Database Table

Configurable via `modularityConfig('blocks_table', 'twill_blocks')`. Defaults to `twill_blocks` for compatibility with Twill-based setups.

### Columns

| Column | Type | Description |
|--------|------|-------------|
| `blockable_id` | string | ID of the owning model |
| `blockable_type` | string | Class of the owning model |
| `position` | integer | Sort order within the same blockable + editor_name |
| `content` | JSON | All block field values as a keyed object |
| `type` | string | Block type identifier — maps to a Blade view |
| `child_key` | string\|null | Identifies which repeater slot this child occupies |
| `parent_id` | integer\|null | `null` = root block; non-null = nested child of the given block |
| `editor_name` | string\|null | Named editor this block belongs to (`'default'` when null) |

## Traits

| Trait | What it adds |
|-------|-------------|
| `HasFiles` | `files()` morph-many to `File` |
| `HasImages` | `medias()` morph-many to `Media` (eager-loaded via `$with`) |
| `HasPresenter` | `$presenter` attribute resolved from config |
| `HasRelated` | `relatedItems()` morph-many to `RelatedItem` |

## Relationships

```php
// Owning model (any model using HasBlocks)
public function blockable(): MorphTo

// Direct child blocks (parent_id = this block's id)
public function children(): HasMany   // → Block
```

## Scopes

### `scopeEditor($query, string $name = 'default')`

Filters blocks by `editor_name`. When `$name` is `'default'`, includes both `editor_name = 'default'` and `editor_name IS NULL`.

```php
// All blocks in the default editor
$model->blocks()->editor()->get();

// All blocks in the sidebar editor
$model->blocks()->editor('sidebar')->get();
```

## Content Access Methods

These helpers read fields out of the `content` JSON column:

### `input(string $name): mixed`

Returns `$this->content[$name]` or `null`.

```php
$block->input('title');       // "Hello World"
$block->input('missing_key'); // null
```

### `translatedInput(string $name, ?string $forceLocale = null): mixed`

Returns a locale-specific value from a translated field. Falls back to `translatable.fallback_locale` when the current locale is missing and `use_property_fallback` is enabled.

```php
// content = { "title": { "en": "Hello", "de": "Hallo" } }
$block->translatedInput('title');        // "Hello" (current locale)
$block->translatedInput('title', 'de');  // "Hallo"
```

### `checkbox(string $name): bool`

Returns `true` when the field is a checked checkbox value.

```php
$block->checkbox('show_border'); // true / false
```

### `browserIds(string $name): array`

Returns the array of IDs stored under `content.browsers.$name`. Used to retrieve browser-type relationship selections.

```php
$block->browserIds('related_products'); // [4, 7, 12]
```

## Rendering

Blocks are rendered through `HasBlocks` on the owning model — not directly from the `Block` model.

```php
// Render all default-editor blocks (with child blocks)
echo $page->renderBlocks();

// Render a named editor
echo $page->renderNamedBlocks('sidebar');

// Render without child blocks
echo $page->renderNamedBlocks('default', renderChilds: false);

// Custom view mappings
echo $page->renderBlocks(true, [
    'hero' => 'blocks.custom-hero',
]);

// Pass extra data to Blade views
echo $page->renderBlocks(true, [], ['theme' => 'dark']);
```

### Render pipeline

For each root block in the named editor, `renderNamedBlocks()`:

1. Finds the block's view via `BlockConfig::findFirstWithType($block->type)->getBlockView($blockViewMappings)`
2. Calls `$class->getData($data, $block)` to enrich the data array
3. Renders the Blade view with `->with('block', $block)`
4. If `$renderChilds = true`, repeats steps 1–3 for every child and appends their rendered HTML

### Presenter

When `modularityConfig('block_editor.block_presenter_path')` is set, the `$presenter` attribute resolves to that class path. This follows the standard Modularous presenter pattern.

## Parent / Child Nesting

Blocks support **one level** of parent/child nesting via `parent_id`:

```
Block { id: 10, type: 'columns', parent_id: null }   ← root
├── Block { id: 11, type: 'column-item', parent_id: 10, child_key: 'items' }
└── Block { id: 12, type: 'column-item', parent_id: 10, child_key: 'items' }
```

- Root blocks have `parent_id = null` and are rendered by `renderNamedBlocks`.
- Child blocks are collected via `$this->blocks->where('parent_id', $block->id)` during rendering.
- `child_key` identifies which repeater slot in the parent's schema this child occupies.

> Nesting is **not recursive** — only one level of parent→child is supported.

## Configuration

| Config key | Default | Description |
|------------|---------|-------------|
| `modularity.blocks_table` | `'twill_blocks'` | Database table name |
| `modularity.block_editor.block_presenter_path` | `null` | Presenter class path for block models |

## Related

- [HasBlocks](/system-reference/backend/entity-traits/secondary/has-blocks) — adds block support to models
- [Repeater](./repeater) — similar concept for repeatable content
