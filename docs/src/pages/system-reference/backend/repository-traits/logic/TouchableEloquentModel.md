---
sidebarPos: 13
sidebarTitle: TouchableEloquentModel
---

# TouchableEloquentModel

**Namespace**: `Unusualify\Modularous\Repositories\Logic\TouchableEloquentModel`

Deferred timestamp touching for parent models. Tracks whether a relationship sync produced changes and calls `$object->touch()` at the end of the save cycle rather than after each individual sync.

## Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$mustTouchEloquentModel` | `bool` | `false` | Accumulates the "should touch" signal across the entire save operation |

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `mustTouchEloquentModel` | `(): void` | Sets `$mustTouchEloquentModel = true` |
| `letEloquentModelBeTouched` | `(bool $value): void` | Only upgrades the flag to `true` — never resets it from `true` to `false`. Called by `Relationships::afterSaveRelationships()` after each sync operation that produced changes. |
| `touchEloquentModel` | `(Model $object): Model` | Calls `$object->touch()` when `$mustTouchEloquentModel` is `true` **or** when `$object->mustTouchable === true`. Returns the model. |

## Why Deferred Touching?

Multiple relationship syncs run in a single save cycle (BelongsToMany, HasMany, MorphMany, etc.). Without deferred touching, each changed sync would individually call `touch()`, resulting in multiple `UPDATE` queries and unnecessary cache invalidations. By accumulating the flag and calling `touch()` once at the end, the parent record's `updated_at` is updated exactly once.

## Usage

```php
// Touching is handled automatically by Relationships::afterSaveRelationships().
// To force a touch regardless of relationship changes:

$repo->mustTouchEloquentModel();
$repo->touchEloquentModel($object);

// To respect a model-level always-touch flag, set on the model:
class Post extends Model
{
    public bool $mustTouchable = true;
}
```
