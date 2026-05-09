---
sidebarPos: 13
sidebarTitle: Model
---

# Model

**File**: `src/Entities/Model.php`  
**Namespace**: `Unusualify\Modularous\Entities`  
**Extends**: `Illuminate\Database\Eloquent\Model`  
**Implements**: `CacheableInterface`, `ModuleableInterface`, `TaggableInterface`  
**Traits**: `HasPresenter`, `IsTranslatable`, `ModelHelpers`, `SoftDeletes`, `TaggableTrait`, `LocaleTags`, `Notifiable`, `HasCaching`, `Traitify`

Abstract base model for all Modularous entities. Every module-generated model extends this class and inherits soft-deletes, tagging, cache management, presenter support, notifications, and trait-hook composition.

## Built-in Behaviour

| Feature | Provider |
|---------|----------|
| Soft deletes | `SoftDeletes` |
| Tagging | `TaggableTrait` + `LocaleTags` |
| Cache invalidation | `HasCaching` |
| Presenter pattern | `HasPresenter` |
| Translation support | `IsTranslatable` |
| Notifications | `Notifiable` |
| Trait method hooks | `Traitify` |

## Methods

### `getFillable(): array`

Returns the model's fillable attributes. For translation models that define a `$baseModuleModel`, automatically populates fillable from the base model's `translatedAttributes` plus `locale` and `active`. Also merges fillable fields from `HasAuthorizable` when that trait is present.

### `tags(): MorphToMany`

Polymorphic many-to-many relationship to `Tag` via the `tagged` pivot table. Table name is read from `modularous.tables.tagged`.

### `newInstance($attributes, $exists): static`

Overrides Laravel's `newInstance` to propagate trait-specific state (e.g. cache warming flags) to freshly constructed instances during `Builder::create()` calls.

### `setPublishStartDateAttribute($value): void`

Mutator that defaults `publish_start_date` to the current time when null.

## Table Name

All entity models resolve their table name via `modularousConfig('tables.{key}')`, falling back to the parent's default.

## Related

- [Entity Traits](/system-reference/backend/entity-traits/overview) — traits composed into models extending this class
- [Repository](/system-reference/repositories) — data access layer for models
