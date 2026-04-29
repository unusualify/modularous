---
sidebarPos: 3
sidebarTitle: HasRelated
---

# Secondary\HasRelated

**Namespace**: `Unusualify\Modularity\Entities\Traits\Secondary\HasRelated`

Links related content via a `RelatedItem` morph-many pivot. Supports named "browser" contexts so a model can have multiple independent related-item groups (e.g., `related_articles`, `similar_products`).

---

## Relationship

```php
public function relatedItems(): MorphMany   // → RelatedItem records
```

---

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `getRelated` | `(string $browserName): Collection` | Returns the related models for the given browser name |
| `loadRelated` | `(string $browserName): void` | Eager-loads related items into the model's relation cache |
| `saveRelated` | `(string $browserName, array $ids): void` | Syncs related items for the given browser name to the provided ID list |
| `clearRelated` | `(string $browserName): void` | Removes all related items for the given browser name |
| `clearAllRelated` | `(): void` | Removes all related items for this model |

---

## Usage

```php
use Unusualify\Modularity\Entities\Traits\Secondary\HasRelated;

class Article extends Model
{
    use HasRelated;
}

// Sync related articles (browser = 'related_articles')
$article->saveRelated('related_articles', [2, 5, 8]);

// Read
$article->getRelated('related_articles');

// Eager load
$article->loadRelated('related_articles');

// Clear one group
$article->clearRelated('related_articles');

// Clear everything
$article->clearAllRelated();
```
