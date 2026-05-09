---
sidebarPos: 1
sidebarTitle: HasPosition
---

# HasPosition

**Namespace**: `Unusualify\Modularous\Entities\Traits\HasPosition`

Manages an integer `position` column for drag-and-drop ordering. Automatically assigns a position on creation and provides a static helper for reordering.

---

## Boot Behavior

| Event | Action |
|-------|--------|
| `creating` | If `position` is not set, assigns `max(position) + 1`. If set but exceeds the current max, adjusts to last position. If set within range, increments all records at or above that position by 1 |

---

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `setNewOrder` | `(array $ids, int $startOrder = 1): int` (static) | Reorders models given an array of IDs in the desired sequence; returns `1` on success |

---

## Scopes

| Scope | Description |
|-------|-------------|
| `scopeOrdered()` | Orders by `{table}.position ASC` |

---

## Usage

```php
use Unusualify\Modularous\Entities\Traits\HasPosition;

class MenuItem extends Model
{
    use HasPosition;
}

// All records ordered by position
MenuItem::ordered()->get();

// Reorder after a drag-and-drop interaction
// $ids is the new ordered array of IDs sent from the frontend
MenuItem::setNewOrder([3, 1, 4, 2]);

// Optional: start numbering from a different offset
MenuItem::setNewOrder([3, 1, 4, 2], startOrder: 0);
```

::: tip Migration note
Your migration must include:
```php
$table->unsignedInteger('position')->default(0);
```
:::
