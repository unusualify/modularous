---
sidebarPos: 2
sidebarTitle: HasNesting
---

# Secondary\HasNesting

**Namespace**: `Unusualify\Modularous\Entities\Traits\Secondary\HasNesting`

Adds nested-set slug traversal and tree persistence for hierarchical models (e.g., category trees, menu items). Builds full slug paths by walking up the ancestor chain and provides a static method to persist reordered trees.

---

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `getNestedSlug` | `(): string` | Returns the full slug path including all ancestor segments (e.g. `"parent/child/grandchild"`) |
| `getAncestorsSlug` | `(): string` | Returns the ancestor portion of the slug path (excludes own slug) |
| `saveTreeFromIds` | `(array $ids, ?int $parentId = null): void` (static) | Persists a new tree order from a nested ID array (drag-and-drop payload) |
| `flattenTree` | `(array $tree, ?int $parentId = null): array` (static) | Flattens a nested tree array to a flat list with `parent_id` set on each item |

---

## Usage

```php
use Unusualify\Modularous\Entities\Traits\Secondary\HasNesting;

class Category extends Model
{
    use HasNesting;
}

// Slug paths
$category->getNestedSlug();      // "electronics/phones/smartphones"
$category->getAncestorsSlug();   // "electronics/phones"

// Save reordered tree (from drag-and-drop frontend payload)
Category::saveTreeFromIds([
    ['id' => 3, 'children' => [
        ['id' => 5],
        ['id' => 7, 'children' => [['id' => 9]]],
    ]],
    ['id' => 1],
]);

// Flatten for processing
$flat = Category::flattenTree($nestedArray);
// [['id' => 3, 'parent_id' => null], ['id' => 5, 'parent_id' => 3], ...]
```
