---
sidebarPos: 8
sidebarTitle: MethodTransformers
---

# MethodTransformers

**Namespace**: `Unusualify\Modularous\Repositories\Logic\MethodTransformers`

The lifecycle-hook dispatcher at the heart of every `Repository`. Composes `ManageTraits` (which enumerates hook methods from loaded traits via naming conventions) and `CacheableTrait`. All repository traits plug into the lifecycle through this class.

## Lifecycle Methods

Each method fans out to every `{hookName}{TraitName}` method discovered by `traitsMethods()`:

| Method | Signature | When called |
|--------|-----------|-------------|
| `prepareFieldsBeforeCreate` | `($fields): array` | Before `model->create()` |
| `prepareFieldsBeforeSave` | `($object, $fields): array` | Before any `$object->save()` — receives and must return modified `$fields` |
| `beforeSave` | `($object, $fields): void` | Just before `$object->save()` |
| `afterSave` | `($object, $fields): void` | Immediately after save — side effects (file moves, pivot syncs, etc.) |
| `afterUpdateBasic` | `($object, $fields): void` | After a basic update (non-form-save path) |
| `afterDelete` | `($object): void` | After soft-delete |
| `afterForceDelete` | `($object): void` | After hard-delete |
| `afterRestore` | `($object): void` | After restore from trash |
| `hydrate` | `($object, $fields): Model` | Sets in-memory relationships on the model before save |
| `getFormFields` | `($object, $schema, $noSerialization): array` | Builds the full form-field payload for the edit form — calls `setColumns()` first |
| `getShowFields` | `($object, $schema): array` | Builds the field payload for a show/detail view |
| `filter` | `($query, $scopes): Builder` | Applies all filter hooks from traits, then processes remaining scopes (scoped methods, LIKE, whereIn, exact-match) |
| `order` | `($query, $orders): Builder` | Applies all order hooks from traits, then applies explicit column→direction orders |
| `getTableFilters` | `($scope): array` | Aggregates filter tab definitions from all traits |
| `getFormActions` | `($scope): array` | Aggregates form action button definitions from all traits |
| `appendFormSchema` | `($scope): array` | Aggregates schema additions to append to the form |
| `prependFormSchema` | `($scope): array` | Aggregates schema additions to prepend to the form |

## Column Management

| Method | Signature | Description |
|--------|-----------|-------------|
| `setColumns` | `($inputs): void` | Calls every `setColumns{Trait}` hook, merges results into `$this->traitColumns` |
| `getColumns` | `(?string $trait): array` | Returns the registered column names for a specific trait (used inside trait hooks to know which inputs they own) |
| `traitHasInput` | `(string $traitClass, string $inputName): bool` | Checks if a given trait has registered a specific input name |
| `anyTraitHasInput` | `(array $traitClasses, string $inputName): bool` | Returns `true` if any of the given traits owns the input name |

## Field Cleanup

`cleanupFields($object, $fields)` runs automatically before every `prepareFieldsBeforeCreate` / `prepareFieldsBeforeSave`:

- **Checkboxes** (`$model->checkboxes`): missing → `false`, present → cast to bool.
- **Nullable** (`$model->nullable`): missing fields → `null`.

## Scope Filtering (in `filter()`)

The `filter()` method processes scopes in three passes:

1. **Trait hooks** — each `filter{Trait}($query, $scopes)` runs first, modifying the query in-place.
2. **Relation scopes** (`addRelation{Relation}`) — resolved via reflection; handles `MorphTo`, `HasOneThrough`, `HasOne`, and `BelongsTo` automatically.
3. **Remaining scopes** — dispatched as:
   - Eloquent scope method if `$model->hasScope($column)`
   - `LIKE` if column starts with `%`
   - Negation (`!value`) → `<>`
   - Array value → `whereIn`
   - Scalar value → `where`

## Status Counts

`getCountByStatusSlug($slug, $scope)` delegates to trait-level `getCountByStatusSlug{Trait}` hooks, falling back to the built-in slugs: `all`, `published`, `draft`, `trash`.

## Usage

```php
// The lifecycle methods are called by Repository internally.
// Override in your repository class to customise behaviour:

class PostRepository extends Repository
{
    use TranslationsTrait, FilesTrait;

    public function prepareFieldsBeforeSaveMyHook($object, $fields): array
    {
        $fields['slug'] = Str::slug($fields['title']);
        return $fields;
    }
}
```
