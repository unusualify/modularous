---
sidebarPos: 7
sidebarTitle: InspectTraits
---

# InspectTraits

**Namespace**: `Unusualify\Modularity\Repositories\Logic\InspectTraits`

Runtime trait introspection for the repository and its model. Used internally by the base `Repository` to discover which lifecycle hooks are available.

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `hasBehavior` | `(string $behavior): bool` | Checks if the repository uses `Repositories\Traits\{Behavior}Trait`. For translation traits, also verifies that the model is translatable. |
| `isTranslatable` | `(string $column): bool` | Checks if a specific column is translatable on the model |
| `isSoftDeletable` | `(): bool` | Checks if the model supports soft deletes |
| `hasModelTrait` | `(string $trait): bool` | Checks if the model class uses a specific trait (fully qualified name) |

## Behavior Check Logic

```php
$repo->hasBehavior('translations');
// 1. Checks: classHasTrait($this, TranslationsTrait::class) → true/false
// 2. If behavior starts with 'translation':
//    also checks $this->model->isTranslatable() → must be true
```

## Usage

```php
// Internally used by the Repository base class:
if ($this->hasBehavior('slugs')) {
    $this->afterSaveSlugsTrait($object, $fields);
}

// Check model capabilities
if ($repo->isTranslatable('title')) {
    // handle translated field
}

if ($repo->isSoftDeletable()) {
    // handle soft delete UI
}

// Check for a specific model trait
if ($repo->hasModelTrait('Unusualify\Modularity\Entities\Traits\HasStateable')) {
    // show state filters
}
```
