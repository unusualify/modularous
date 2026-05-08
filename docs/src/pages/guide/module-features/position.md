---
outline: deep
sidebarPos: 8
---

# Position

The Position feature adds ordering via a `position` column. It is **entity-only** — no repository trait or Hydrate.

## Entity Trait: HasPosition

Add the `HasPosition` trait to your model:

```php
<?php

namespace Modules\Category\Entities;

use Unusualify\Modularity\Entities\Model;
use Unusualify\Modularity\Entities\Traits\HasPosition;

class Category extends Model
{
    use HasPosition;
}
```

### Database

Add a `position` column to your table (integer):

```php
Schema::table('categories', function (Blueprint $table) {
    $table->integer('position')->default(0);
});
```

### Boot Logic

- On **creating**: Sets `position` to the last position + 1 if not set; or inserts at the given position and shifts others

### Methods

- **scopeOrdered** — Orders by `position`
- **setNewOrder($ids, $startOrder = 1)** — Reorders records by ID array (e.g. after drag-and-drop)

### Example: Reordering

```php
// Reorder categories by ID
Category::setNewOrder([3, 1, 2, 4]);  // position 1, 2, 3, 4
```

### Example: Ordered Query

```php
$categories = Category::ordered()->get();
```

## Repository Trait

None. Position is managed at the entity level.

## Input Config

None. Position is typically updated via a separate reorder endpoint, not a form input.

## Hydrate

None.
