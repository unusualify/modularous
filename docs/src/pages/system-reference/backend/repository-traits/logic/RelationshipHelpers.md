---
sidebarPos: 10
sidebarTitle: RelationshipHelpers
---

# RelationshipHelpers

**Namespace**: `Unusualify\Modularity\Repositories\Logic\RelationshipHelpers`

Utility methods for discovering relationships on a model and resolving foreign key names. Used internally by `Relationships` and `MethodTransformers`.

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `getDefinedRelations` | `($relations = null): array` | Uses PHP reflection to find all public, zero-parameter model methods whose return type matches `Illuminate\Database\Eloquent\Relations\{...}`. Pass a string or array to filter by specific relation type(s). |
| `definedRelations` | `($relations = null): array` | Delegates to `$model->definedRelations()` if the model defines it, otherwise calls `getDefinedRelations()`. |
| `getRelationForeignKey` | `($relation): string` | Dispatches to the appropriate foreign-key resolver based on relation type (`BelongsTo`, `BelongsToMany`, `HasMany`). Throws `InvalidArgumentException` for unsupported types. |

## Foreign Key Resolvers (private)

| Method | Relation Type | Returns |
|--------|---------------|---------|
| `getForeignKeyBelongsTo` | `BelongsTo` | `getForeignKeyName()` — the column on the owning model |
| `getForeignKeyBelongsToMany` | `BelongsToMany` | `getRelatedPivotKeyName()` — the related ID column on the pivot |
| `getForeignKeyHasMany` | `HasMany` | `getForeignKeyName()` — the column on the child model |

## Usage

```php
// Discover all BelongsToMany relationships on the model
$pivotRelations = $repo->definedRelations('BelongsToMany');

// Discover multiple types at once
$syncable = $repo->definedRelations(['BelongsToMany', 'MorphToMany']);

// Resolve the foreign key for a runtime relation instance
$fk = $repo->getRelationForeignKey($post->categories());
// → 'category_id' (pivot's related key)
```
