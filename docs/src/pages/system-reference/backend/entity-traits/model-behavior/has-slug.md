---
sidebarPos: 3
sidebarTitle: HasSlug
---

# HasSlug

**Namespace**: `Unusualify\Modularous\Entities\Traits\HasSlug`

Generates and stores URL slugs in a dedicated `{Model}Slug` model. Supports multi-locale slugs, UTF-8 transliteration, and automatic slug suffixing to avoid collisions. Overrides `resolveRouteBinding()` to look up models by slug rather than primary key.

---

## Boot Behavior

| Event | Action |
|-------|--------|
| `saved` | Calls `setSlugs()` to generate/update slug records |
| `restored` | Calls `setSlugs($restoring = true)` to re-activate the slug |

---

## Relationship

```php
public function slugs(): HasMany   // → {Model}Slug model, one record per locale
```

---

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `setSlugs` | `(bool $restoring = false): void` | Generates/updates slug records for all locales from `$slugAttributes` |
| `getSlug` | `(?string $locale = null): string` | Returns the active slug for the locale (falls back to `fallback_locale` when configured) |
| `getActiveSlug` | `(?string $locale = null): ?object` | Returns the active `Slug` record for the locale |
| `getFallbackActiveSlug` | `(): ?object` | Returns the active slug in the fallback locale |
| `getSlugsTable` | `(): string` | Returns the database table name for this model's slugs |
| `getForeignKey` | `(): string` | Returns the foreign key column name used in the slugs table |
| `resolveRouteBinding` | `(mixed $value, ?string $field = null): static` | Resolves the model from a slug value (published + visible scope applied) |
| `getUtf8Slug` | `(string $str, array $options = []): string` | Converts a UTF-8 string to a URL-safe slug using a built-in character map |
| `disableLocaleSlugs` | `(string $locale, int $exceptId = 0): void` | Deactivates all slugs for a locale except the given ID |

---

## Scopes

| Scope | Description |
|-------|-------------|
| `scopeExistsSlug($query, $slug)` | Models with an active slug matching the value in the current locale |
| `scopeExistsInactiveSlug($query, $slug)` | Models with any (active or inactive) matching slug |
| `scopeExistsFallbackLocaleSlug($query, $slug)` | Models with an active slug in the fallback locale |

---

## Computed Attributes

| Attribute | Description |
|-----------|-------------|
| `slug` | Virtual — returns `getSlug()` for the current locale |

---

## Configuration

| Property | Type | Description |
|----------|------|-------------|
| `$slugAttributes` | `array` | Fields used to build the slug (e.g. `['title']`). First element is the slug source, additional elements are dependency fields |
| `$slugModelClass` | `string\|null` | Override the auto-resolved `{Model}Slug` class |
| `$slugForeignKey` | `string\|null` | Override the foreign key column name in the slugs table |

```php
// In your model
protected $slugAttributes = ['title'];
// Or with a dependency (e.g., scoped to a category)
protected $slugAttributes = ['title', 'category_id'];
```

---

## Usage

```php
use Unusualify\Modularous\Entities\Traits\HasSlug;

class Post extends Model
{
    use HasSlug;

    protected $slugAttributes = ['title'];
}

// Reading slugs
$post->slug;              // current locale
$post->getSlug();         // same
$post->getSlug('fr');     // French slug

// Checking existence
Post::existsSlug('my-post')->first();

// Route model binding (automatic via resolveRouteBinding)
// Route::get('/posts/{post}', ...) → looks up by slug
```
