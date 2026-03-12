---
sidebarPos: 4
sidebarTitle: Repositories
---

# Repositories

Repositories are the single data access layer. All create/update/delete logic must pass through repository methods. Controllers never access Eloquent models directly.

## Controller Usage

```php
// From PanelController (base for all module controllers)
$this->repository  // Resolved by route name via Finder
```

## Create Lifecycle

Order of execution in `Repository::create()`:

1. `prepareFieldsBeforeCreate($fields)`
2. `model->create($fields)` — creates DB record
3. `beforeSave($object, $original_fields)`
4. `prepareFieldsBeforeSave($object, $fields)`
5. `$object->save()`
6. `afterSave($object, $fields)`
7. `dispatchEvent($object, 'create')`

## Method Transformers

Override these in the repository or via transformer classes to intercept lifecycle:

| Hook | When |
|------|------|
| `prepareFieldsBeforeCreate` | Before DB insert |
| `beforeSave` | After create, before save |
| `prepareFieldsBeforeSave` | Before save (can modify fields) |
| `afterSave` | After save |
| `beforeCreate` / `afterCreate` | Wrapped around create |
| `beforeUpdate` / `afterUpdate` | Wrapped around update |

## Logic Traits

`Repository` uses these traits from `Repositories/Logic/`:

| Trait | Purpose |
|-------|---------|
| QueryBuilder | Query building, filters |
| MethodTransformers | Lifecycle hooks |
| Relationships | Relationship handling |
| RelationshipHelpers | Relationship utilities |
| Schema | Schema handling, chunkInputs |
| InspectTraits | Trait inspection |
| CountBuilders | Count queries |
| Dates | Date handling |
| DispatchEvents | Event dispatching |
| CollationSelector | Collation selection |
| CacheableTrait | Caching |
| TouchableEloquentModel | Touch timestamps |

## Reserved Fields

Fields in `getReservedFields()` are excluded from `model->create()`. Override `$ignoreFieldsBeforeSave` to add more.

## Fields Groups

Use `$fieldsGroups` to group form fields. Schema is chunked by groups for `prepareFieldsBeforeCreate` / `prepareFieldsBeforeSave`.
