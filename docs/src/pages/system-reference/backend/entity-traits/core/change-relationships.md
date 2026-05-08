---
sidebarPos: 1
sidebarTitle: ChangeRelationships
---

# Core\ChangeRelationships

**Namespace**: `Unusualify\Modularity\Entities\Traits\Core\ChangeRelationships`

Lightweight change-tracking for Eloquent relationships during a single request cycle. Used internally by `HasFileponds` and `Core\ModelHelpers` to flag which relationship collections changed so event listeners can react without re-querying.

---

## Properties

| Property | Type | Description |
|----------|------|-------------|
| `$changedRelationships` | `array` | Map of `relationship name → changed records` |

---

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `setChangedRelationships` | `(array $relationships): void` | Replaces the entire changed-relationships map |
| `addChangedRelationships` | `(string $name, mixed $relationship): void` | Adds a single relationship entry |
| `mergeChangedRelationships` | `(string $name, Collection $relationships): void` | Merges new records into an existing entry |
| `getChangedRelationships` | `(): array` | Returns the full map |
| `wasChangedRelationships` | `(string\|array\|null $relationships = null): bool` | Returns `true` if any (or the specified) relationship changed |

---

## Usage

```php
use Unusualify\Modularity\Entities\Traits\Core\ChangeRelationships;

class Article extends Model
{
    use ChangeRelationships;
}

// Mark a relationship as changed
$article->addChangedRelationships('images', $newImages);
$article->mergeChangedRelationships('images', $additionalImages);

// Check in a listener
if ($article->wasChangedRelationships()) {
    // some relationship changed
}

if ($article->wasChangedRelationships('images')) {
    // images specifically changed
}

if ($article->wasChangedRelationships(['images', 'files'])) {
    // images OR files changed
}
```
