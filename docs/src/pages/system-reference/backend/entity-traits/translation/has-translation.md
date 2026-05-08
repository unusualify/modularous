---
sidebarPos: 1
sidebarTitle: HasTranslation
---

# HasTranslation

**Namespace**: `Unusualify\Modularity\Entities\Traits\HasTranslation`

Extends `Astrotomic\Translatable\Translatable` with Modularous-aware overrides for locale-keyed attribute filling, attribute transformation, and translation class resolution.

---

## Boot Behavior

| Event | Action |
|-------|--------|
| `saving` | When model is a Pivot, runs `handleTranslationAttributes` to route locale-keyed arrays correctly |
| `deleting` / `forceDeleting` | Calls `deleteTranslations()` to remove all translation records |

---

## Configuration

```php
// In your model
public $translatedAttributes = ['title', 'body', 'slug'];

// Enable transformed fill (locale-keyed arrays)
protected bool $transformTranslatedAttributes = true;
```

---

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `fill` | `(array $attributes): static` | Overrides parent `fill` to route locale-keyed arrays through `handleTranslationAttributes` |
| `setAttribute` | `(string $key, mixed $value): static` | Falls through to Translatable for translated keys; parent for non-translated |
| `translatedAttribute` | `(string $key, ?string $locale = null): mixed` | Returns the translated value for a key; without locale returns a Collection of all translations |
| `getTranslatedAttribute` | `(string $key, ?string $locale = null): mixed` | Returns the translated value for the current (or given) locale via `translate()` |
| `getTranslatedAttributes` | `(): array` | Returns `$this->translatedAttributes` |
| `getTranslationModelNameDefault` | `(): string` | Resolves the Translation model class (`{Model}Translation`) from the module or capsule namespace |
| `getActiveLanguages` | `(): Collection` | Returns all configured locales with their `published` status for this model |
| `hasActiveTranslation` | `(?string $locale = null): bool` | Returns `true` if the model has an active translation for the locale |
| `disableTranslationFilling` | `(): void` | Temporarily disables locale-keyed fill routing |
| `enableTranslationFilling` | `(): void` | Re-enables locale-keyed fill routing |

---

## Scopes

| Scope | Description |
|-------|-------------|
| `scopeWithActiveTranslations(?string $locale)` | Eager-loads active translations for the given locale |
| `scopeOrderByTranslation(string $key, string $dir = 'ASC', ?string $locale)` | Orders by a translated column via JOIN |
| `scopeOrderByRawByTranslation(string $rawOrder, string $groupBy, ?string $locale)` | Orders by a raw expression on the translations table |

---

## Usage

```php
use Unusualify\Modularity\Entities\Traits\HasTranslation;

class Article extends Model
{
    use HasTranslation;

    public $translatedAttributes = ['title', 'body'];
}

// Filling translations
$article->fill([
    'en' => ['title' => 'Hello', 'body' => 'World'],
    'fr' => ['title' => 'Bonjour', 'body' => 'Monde'],
]);
$article->save();

// Reading
$article->title;                            // current locale
$article->translatedAttribute('title', 'fr'); // 'Bonjour'
$article->getActiveLanguages();
// [['value' => 'en', 'published' => true], ...]

// Checking
$article->hasActiveTranslation('fr');       // true

// Querying
Article::withActiveTranslations()->get();
Article::withActiveTranslations('de')->get();
Article::orderByTranslation('title')->get();
```
