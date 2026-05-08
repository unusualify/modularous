---
sidebarPos: 1
sidebarTitle: HasBlocks
---

# Secondary\HasBlocks

**Namespace**: `Unusualify\Modularity\Entities\Traits\Secondary\HasBlocks`

Attaches ordered `Block` morph records for flexible content composition — page builder-style blocks where each block has a name, position, and rendered output.

---

## Relationship

```php
public function blocks(): MorphMany   // → Block, ordered by position ASC
```

---

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `renderBlocks` | `(): string` | Renders all blocks to HTML in position order |
| `renderNamedBlocks` | `(string $name, bool $renderChilds = false): string` | Renders only blocks matching the given name; optionally includes child blocks |

---

## Usage

```php
use Unusualify\Modularity\Entities\Traits\Secondary\HasBlocks;

class Page extends Model
{
    use HasBlocks;
}

// Query blocks
$page->blocks()->get();
$page->blocks()->where('name', 'hero')->first();

// Render all blocks
echo $page->renderBlocks();

// Render a named section
echo $page->renderNamedBlocks('hero');

// Include child blocks
echo $page->renderNamedBlocks('content', renderChilds: true);
```
