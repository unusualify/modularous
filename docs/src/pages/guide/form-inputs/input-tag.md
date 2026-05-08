---
sidebarPos: 35
sidebarTitle: Tag
---

# Tag

The `tag` input type renders `VInputTag`, a tag picker backed by the Modularous `Tag` entity. It auto-resolves the list of available tags and the update endpoint from the current module/route context, or from an explicit `taggable` model class.

## Hydrate

**Class:** `TagHydrate`
**Config type:** `tag`
**Output type:** `input-tag` → `VInputTag`

The hydrate:

1. Sets `returnObject: false`, `chips: false`, `multiple: false` as base defaults
2. **Auto-resolution via `_moduleName` / `_routeName`** (set automatically by the module config pipeline):
   - Resolves `endpoint` → `{module}.{route}.tags` action URL
   - Resolves `updateEndpoint` → `{module}.{route}.tagsUpdate` action URL
   - Fetches `items` via `repository->getTags()`
   - Sets `taggable` to the repository's model class
3. **Explicit `taggable` class** (when `_moduleName` is absent):
   - Fetches items via `$taggableModel->localeTagsList()` (translated) or `Tag::whereNamespace($taggable)->get()`
4. Falls back `updateEndpoint` to `route('admin.tag.update')` if not resolved
5. Sets `default` to the first item's `itemValue` (non-translated, non-empty items only)

### Translated tags

When `translated: true` is set, the hydrate adds a `cacheKey` (locale-scoped) and an `updatePayload` carrying the current locale so tag updates are applied to the correct translation.

## Usage

### Auto-resolved (standard module context)

```php
[
    'type'  => 'tag',
    'name'  => 'tags',
    'label' => 'Tags',
]
```

### Translated tags

```php
[
    'type'       => 'tag',
    'name'       => 'tags',
    'label'      => 'Tags',
    'translated' => true,
]
```

### Explicit taggable model

```php
[
    'type'     => 'tag',
    'name'     => 'tags',
    'label'    => 'Tags',
    'taggable' => \App\Models\Post::class,
]
```

## Schema Defaults

| Key | Default | Description |
|-----|---------|-------------|
| `itemValue` | `'id'` | Field used as the tag value |
| `itemTitle` | `'name'` | Field displayed for each tag |
| `cascadeKey` | `'items'` | Key used when cascading tag lists |
| `returnObject` | `false` | Return tag ID rather than full tag object |
| `chips` | `false` | Display selected tags as chips |
| `multiple` | `false` | Allow multiple tag selection |
| `default` | first item's `itemValue` | Pre-selected tag (non-translated, non-empty only) |

## See Also

- [Tagger](/guide/form-inputs/input-tagger) — Free-form tag creation input (different from Tag)
- [Hydrates reference](/system-reference/hydrates) — Resolution table and schema contract
