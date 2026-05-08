---
sidebarPos: 1
sidebarTitle: Overview
sidebarGroupTitle: Logic Traits
---

# Logic Repository Traits

These traits live in the `Repositories\Logic` namespace and are composed directly into the base `Repository` class. They are **not** opt-in — every repository gets them automatically. Together they provide the full infrastructure layer: query building, relationship hydration, lifecycle hooks, date normalisation, event dispatch, collation, caching, and timestamp management.

## Trait Reference

| Trait | Purpose |
|-------|---------|
| [QueryBuilder](./QueryBuilder) | Paginated listing, single-record lookup, multi-ID fetching, and flat-list helpers |
| [MethodTransformers](./MethodTransformers) | Lifecycle hook dispatcher — fans out to every `{hookName}{Trait}` method across loaded traits |
| [Relationships](./Relationships) | Syncs all Eloquent relationship types (BelongsToMany, HasMany, MorphMany, MorphTo, MorphToMany) on save |
| [RelationshipHelpers](./RelationshipHelpers) | Reflection-based relationship discovery and foreign key resolution |
| [Schema](./Schema) | Active schema management and input chunking helpers |
| [CountBuilders](./CountBuilders) | Cached aggregate counts for status tabs (all, published, draft, trash) |
| [Dates](./Dates) | Normalises date fields to `Y-m-d H:i:s` before save |
| [DispatchEvents](./DispatchEvents) | Dispatches domain events (create/update/delete/restore) after database commit |
| [CollationSelector](./CollationSelector) | Applies explicit MySQL collation to LIKE search queries on text columns |
| [CacheableTrait](./CacheableTrait) | Relationship-aware caching for index and record queries |
| [InspectTraits](./InspectTraits) | Runtime introspection — checks whether repository or model uses a given trait |
| [TouchableEloquentModel](./TouchableEloquentModel) | Deferred `updated_at` touching — fires exactly once after all relationship syncs |
