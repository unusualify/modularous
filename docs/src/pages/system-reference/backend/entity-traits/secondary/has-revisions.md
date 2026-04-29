---
sidebarPos: 5
sidebarTitle: HasRevisions
---

# Secondary\HasRevisions

**Namespace**: `Unusualify\Modularity\Entities\Traits\Secondary\HasRevisions`

Stores an ordered revision history as a `HasMany` relationship. Revision records are automatically resolved from the module's `Revisions/` namespace or from the active Twill capsule.

---

## Relationship

```php
public function revisions(): HasMany   // → Revision, ordered by created_at DESC
```

---

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `revisionsArray` | `(): array` | Returns revisions formatted for CMS views — includes `id`, `author`, `datetime`, and a `label` marking the latest as `"current"` |

---

## Scopes

| Scope | Description |
|-------|-------------|
| `scopeMine($query)` | Filters models that have at least one revision belonging to the currently authenticated CMS user |

---

## Revision Model Resolution

The trait resolves the revision model in this order:

1. `{config_namespace}\Models\Revisions\{ModelName}Revision` — if the class exists
2. Falls back to `TwillCapsules::getCapsuleForModel()->getRevisionModel()`

---

## Usage

```php
use Unusualify\Modularity\Entities\Traits\Secondary\HasRevisions;

class Article extends Model
{
    use HasRevisions;
}

// Eager-load revisions (newest first)
$article->load('revisions');

// Access revision history
foreach ($article->revisions as $revision) {
    echo $revision->user->name . ' at ' . $revision->created_at;
}

// CMS-formatted array
$history = $article->revisionsArray();
// [
//   ['id' => 5, 'author' => 'Alice', 'datetime' => '2024-...', 'label' => 'current'],
//   ['id' => 4, 'author' => 'Bob',   'datetime' => '2024-...', 'label' => ''],
// ]

// Scope: models edited by current user
Article::mine()->get();
```
