---
sidebarPos: 14
sidebarTitle: NestedsetCollection
---

# NestedsetCollection

**File**: `src/Entities/NestedsetCollection.php`
**Namespace**: `Unusualify\Modularous\Entities`
**Extends**: `Kalnoy\Nestedset\Collection`

Custom Eloquent collection for nested-set models. Extends the Kalnoy nested-set collection with a more permissive `toTree()` that handles partial trees where some parent nodes are missing from the result set.

## Methods

### `toTree($root = false): NestedsetCollection`

Builds a tree from the flat list. Requires `id`, `_lft`, and `parent_id` columns. Unlike the parent implementation, nodes whose parent is not in the current result set are promoted to root level rather than being discarded.

**Parameters**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$root` | `mixed` | Root node ID to scope the tree, or `false` for all roots |

## Why it Exists

The upstream `toTree()` assumes the first loaded node is a root. When you load a partial subtree (e.g. only children of node 3), nodes whose `parent_id` is not in the loaded set would be silently dropped. This override treats any node whose `parent_id` is absent from the loaded IDs as a root:

```php
public function toTree($root = false): NestedsetCollection
{
    // ...
    foreach ($this->items as $node) {
        if ($node->getParentId() == $root) {
            $items[] = $node;
        } elseif (! in_array($node->getParentId(), $ids)) {
            $items[] = $node;  // treat orphaned parent as root
        }
    }
    // ...
}
```

## Usage

`HasNesting::newCollection()` returns this class automatically — you do not instantiate it directly.

```php
$tree = Category::all()->toTree(); // → NestedsetCollection with children set
```

## Related

- [HasNesting](/system-reference/backend/entity-traits/secondary/has-nesting) — trait that adds nested-set behaviour to models
- `kalnoy/nestedset` — the underlying nested-set package (`NodeTrait`, `Collection`)
