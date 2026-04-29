---
sidebarPos: 2
sidebarTitle: IsTranslatable
---

# IsTranslatable

**Namespace**: `Unusualify\Modularity\Entities\Traits\IsTranslatable`

A single-method detection helper. Does not add relationships, boot hooks, or any storage. Use it alongside `HasTranslation` to safely check at runtime whether a model is translatable without assuming the trait is present.

---

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `isTranslatable` | `(array\|string\|null $columns = null): bool` | Returns `true` if the model uses `HasTranslation`, has `$translatedAttributes`, and — when `$columns` is provided — those columns are in `$translatedAttributes` |

### Checks performed

1. Model uses `Unusualify\Modularity\Entities\Traits\HasTranslation`
2. Model has the `translatedAttributes` property
3. If `$columns` is given, at least one column must be in `translatedAttributes`

---

## Usage

```php
use Unusualify\Modularity\Entities\Traits\HasTranslation;
use Unusualify\Modularity\Entities\Traits\IsTranslatable;

class Article extends Model
{
    use HasTranslation, IsTranslatable;

    public $translatedAttributes = ['title', 'body'];
}

$article->isTranslatable();           // true — model uses HasTranslation
$article->isTranslatable('title');    // true — title is translatable
$article->isTranslatable(['title', 'status']); // true — at least one is translatable
$article->isTranslatable('status');   // false — status is not in translatedAttributes
$article->isTranslatable('status');   // false
```

::: tip Usage in generic code
Useful in repositories and transformers that handle both translatable and non-translatable models:
```php
if ($model->isTranslatable()) {
    $query->withActiveTranslations();
}
if ($model->isTranslatable('title')) {
    $query->orderByTranslation('title');
}
```
:::
