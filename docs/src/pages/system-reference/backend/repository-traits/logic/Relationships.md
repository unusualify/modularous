---
sidebarPos: 11
sidebarTitle: Relationships
---

# Relationships

**Namespace**: `Unusualify\Modularity\Repositories\Logic\Relationships`

Handles automatic synchronisation of all Eloquent relationship types when a record is saved. Composes `CheckSnapshot` and `ResolveConnector` to support special snapshot-sourced `HasMany` children and connector-resolved repositories.

## Lifecycle Hooks

| Hook | Description |
|------|-------------|
| `afterSaveRelationships` | Main sync handler — iterates all relationship types and syncs them from `$fields` |
| `prepareFieldsBeforeSaveRelationships` | Pre-processes `HasMany` fields: if the related model uses `HasSnapshot`, numeric IDs are wrapped into the expected snapshot source format |
| `getFormFieldsRelationships` | Loads all relationship values back into `$fields` for form editing |
| `afterForceDeleteRelationships` | Detaches all `BelongsToMany` relations when a record is hard-deleted |

## Relationship Type Handling in `afterSaveRelationships`

| Type | Sync Method | Notes |
|------|-------------|-------|
| `MorphToMany` | `$object->{relation}()->sync($ids)` | Tags are excluded (`'tags'` is handled by `TagsTrait`) |
| `MorphTo` | Sets `_type` / `_id` columns directly on the model | Iterates configured types and matches from `$fields` |
| `BelongsToMany` | `$object->{relation}()->sync($payload)` | Payload may be a Collection, plain array, or pivot-keyed array |
| `HasMany` | Create / Update / Delete via the related `Repository` | Existing IDs not in `$fields` are deleted via `bulkDelete()` |
| `MorphMany` | Create / Update / Delete directly on the related model | Existing IDs not in `$fields` are deleted via `whereIn(...)->delete()` |

Whenever a sync produces changes (`attached`, `updated`, or `detached` > 0), `TouchableEloquentModel::letEloquentModelBeTouched(true)` is called to update the parent's timestamps.

## Relation Discovery

The trait provides helpers used by `MethodTransformers`:

| Method | Returns | Description |
|--------|---------|-------------|
| `getBelongsToManyRelations` | `array` | All `BelongsToMany` methods on the model |
| `getHasManyRelations` | `array` | All `HasMany` methods on the model |
| `getMorphManyRelations` | `array` | All `MorphMany` methods on the model |
| `getMorphToManyRelations` | `array` | All `MorphToMany` methods on the model |
| `getMorphToRelations` | `array` | Parsed from `morphTo` input types in `inputs()` schema — returns `['morphToName' => [{name, model}, ...]]` |

## Usage

```php
// Relationships are synced automatically during Repository::update() / create().
// To define which relationships are managed, declare them on the model:

class Post extends Model
{
    public function tags(): MorphToMany { ... }
    public function categories(): BelongsToMany { ... }
    public function comments(): HasMany { ... }
}

// The form schema must include input names matching the relationship method names:
// ['type' => 'select', 'name' => 'categories', 'multiple' => true, ...]
```
