---
sidebarPos: 6
sidebarTitle: LocaleTags
---

# Core\LocaleTags

**Namespace**: `Unusualify\Modularous\Entities\Traits\Core\LocaleTags`

Locale-scoped tagging backed by the shared `tagged` pivot table. Tags are namespaced to the model class and filtered by locale so the same tag slug can exist in multiple languages without collision. Extends the Eloquent tagging convention from Cviebrock/Spatie tagging packages.

---

## Boot Behavior

| Event | Action |
|-------|--------|
| `retrieved` | Registers `LocaleTagsCast` on the `locale_tags_payload` field when `$loadLocalizedTags = true` |
| `saving` | Captures `locale_tags_payload` into `$localeTagsUpdatingPayload` and removes from attributes |
| `saved` | If `$localeTagsUpdatingPayload` is set, calls `setLocaleTags` per locale |

---

## Relationship

```php
public function localeTags(?string $locale = null): MorphToMany
```

Returns tags for the given locale (defaults to `app()->getLocale()`).

---

## Static Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$localeTagsField` | `string` | `'locale_tags_payload'` | Virtual fillable field that triggers a tag sync on save |
| `$loadLocalizedTags` | `bool` | `false` | Enable `LocaleTagsCast` on retrieval |

---

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `tagLocale` | `(array\|string $tags, ?string $locale = null): bool` | Adds locale-scoped tags |
| `untagLocale` | `(array\|string\|null $tags = null, ?string $locale = null): bool` | Removes locale-scoped tags (all if `null`) |
| `addLocaleTag` | `(string $name, ?string $locale): void` | Adds a single tag, creating the `Tag` record if needed |
| `removeLocaleTag` | `(string $name, ?string $locale): void` | Removes a single tag |
| `setLocaleTags` | `(array\|string $tags, string $type = 'name', ?string $locale = null): bool` | Syncs the full tag list for a locale (adds missing, removes extra) |
| `allLocaleTags` | `(?string $locale = null): Builder` (static) | Query builder for all tags of this model class in a given locale |
| `localeTagsList` | `(): Collection` | Returns a locale → `Collection<Tag>` map for all active locales |
| `getLocaleTagsDictionary` | `(): array` | Override to provide a custom slug-generation dictionary |
| `generateLocaleTagsSlug` | `(string $name, ?string $locale = null): string` | Generates a locale-aware slug for a tag name |

---

## Scopes

| Scope | Description |
|-------|-------------|
| `scopeWhereLocaleTag($tags, $type, $locale)` | Models that have ALL of the given locale tags |
| `scopeWithLocaleTag($tags, $type, $locale)` | Models that have ANY of the given locale tags |
| `scopeWithoutLocaleTag($tags, $type, $locale)` | Models that have NONE of the given locale tags |

---

## Configuration

```php
// Enable virtual fillable field (required for save-time tag sync)
protected bool $allowLocaleTagsFillable = true;

// Enable auto-cast on retrieval
public static bool $loadLocalizedTags = true;
```

---

## Usage

```php
use Unusualify\Modularous\Entities\Traits\Core\LocaleTags;

class Article extends Model
{
    use LocaleTags;

    public static bool $loadLocalizedTags = true;
    protected bool $allowLocaleTagsFillable = true;
}

// Tag in English
$article->tagLocale(['laravel', 'php'], 'en');

// Tag in French
$article->tagLocale(['framework'], 'fr');

// Sync (add missing, remove extra)
$article->setLocaleTags(['laravel', 'api'], locale: 'en');

// Remove all tags for a locale
$article->untagLocale(locale: 'en');

// Query
Article::whereLocaleTag('laravel', 'slug', 'en')->get();
Article::withLocaleTag(['php', 'laravel'], 'slug', 'en')->get();
Article::withoutLocaleTag('deprecated', 'slug', 'en')->get();

// Get all tags by locale
$article->localeTagsList();
// Collection: ['en' => [...tags], 'fr' => [...tags]]

// Save via fillable (triggers setLocaleTags per locale)
$article->fill(['locale_tags_payload' => ['en' => ['laravel'], 'fr' => ['php']]]);
$article->save();
```
