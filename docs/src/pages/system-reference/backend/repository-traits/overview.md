---
sidebarPos: 1
sidebarTitle: Overview
---

# Repository Logic Traits

Repository traits extend the base `Repository` class with domain-specific persistence logic. While [Entity Traits](../entity-traits/overview) define model-level behavior (relationships, scopes, accessors), repository traits handle **how data flows in and out** — form field hydration, after-save side effects, column detection, table filters, and caching.

Every repository trait follows a **naming convention** that the base `Repository` automatically discovers and invokes at the correct lifecycle stage:

| Convention | Signature | When Called |
|------------|-----------|-------------|
| `setColumns{Trait}` | `($columns, $inputs): array` | During boot — registers which form input names this trait manages |
| `prepareFieldsBeforeCreate{Trait}` | `($fields): array` | Before a new record is inserted |
| `prepareFieldsBeforeSave{Trait}` | `($object, $fields): array` | Before any save (create or update) |
| `beforeSave{Trait}` | `($object, $fields): void` | Just before the Eloquent `save()` call |
| `afterSave{Trait}` | `($object, $fields): void` | Immediately after `save()` — side effects (pivot syncing, file moves, etc.) |
| `hydrate{Trait}` | `($object, $fields): Model` | Sets in-memory relationships before save (preview/validation) |
| `getFormFields{Trait}` | `($object, $fields, $schema): array` | Populates form fields when editing an existing record |
| `afterDelete{Trait}` | `($object): void` | Cleanup after soft-delete |
| `afterRestore{Trait}` | `($object): void` | Restoration hook |
| `filter{Trait}` | `($query, &$scopes): void` | Applies default query scopes/filters on index listings |
| `order{Trait}` | `($query, &$orders): void` | Applies ordering logic |
| `getTableFilters{Trait}` | `($scope): array` | Returns filter tab definitions for the data table UI |
| `getFormActions{Trait}` | `($scope): array` | Returns form action button definitions |

## Trait Groups

| Group | Namespace | Purpose |
|-------|-----------|---------|
| [Media](#media-traits) | `Repositories\Traits\` | Persist files, images, and Filepond uploads |
| [Relationships](#relationship-traits) | `Repositories\Traits\` | Save assignment, authorization, and creator data |
| [Content](#content-traits) | `Repositories\Traits\` | Slugs, translations, tags, and spreadable JSON |
| [State](#state-traits) | `Repositories\Traits\` | State machine filter lists and table filters |
| [Payment](#payment-traits) | `Repositories\Traits\` | Price and payment record management |
| [Processes](#process-traits) | `Repositories\Traits\` | Workflow processes and repeater blocks |
| [OAuth](#oauth-traits) | `Repositories\Traits\` | Social login user lookup and creation |
| [Logic](#logic-traits) | `Repositories\Logic\` | Caching layer and trait introspection |

---

## Media Traits

| Trait | Page | Summary |
|-------|------|---------|
| `FilepondsTrait` | [Media →](./media) | Persists Filepond temporary uploads to permanent storage after save |
| `FilesTrait` | [Media →](./media) | Syncs `File` model attachments through the `fileables` pivot |
| `ImagesTrait` | [Media →](./media) | Syncs `Media` model attachments through the `mediables` pivot |

## Relationship Traits

| Trait | Page | Summary |
|-------|------|---------|
| `AssignmentTrait` | [Relationships →](./relationships) | Provides assignment form fields, default filters, and table filter tabs |
| `AuthorizableTrait` | [Relationships →](./relationships) | Hydrates authorization record fields and adds authorized/unauthorized filters |
| `CreatorTrait` | [Relationships →](./relationships) | Applies creator-based access scope and prepends creator form input |

## Content Traits

| Trait | Page | Summary |
|-------|------|---------|
| `SlugsTrait` | [Content →](./content) | Persists locale-aware slugs and resolves slug-based lookups |
| `SpreadableTrait` | [Content →](./content) | Moves spreadable fields into/from the JSON `Spread` record |
| `TagsTrait` | [Content →](./content) | Syncs tags (with locale support) and provides tag query helpers |
| `TranslationsTrait` | [Content →](./content) | Prepares per-locale translation fields and handles translatable search/ordering |

## State Traits

| Trait | Page | Summary |
|-------|------|---------|
| `StateableTrait` | [State →](./state) | Builds state filter lists and table filter tabs from the model's state machine |

## Payment Traits

| Trait | Page | Summary |
|-------|------|---------|
| `PricesTrait` | [Payment →](./payment) | Creates, updates, and deletes morphed `Price` records with currency exchange |
| `PaymentTrait` | [Payment →](./payment) | Orchestrates payment price calculation, payment service integration, and pay action |

## Process Traits

| Trait | Page | Summary |
|-------|------|---------|
| `ProcessableTrait` | [Processes →](./processes) | Auto-creates workflow processes and hydrates process form fields |
| `RepeatersTrait` | [Processes →](./processes) | Persists nested repeater JSON blocks with locale and media support |

## OAuth Traits

| Trait | Page | Summary |
|-------|------|---------|
| `OauthTrait` | [OAuth →](./oauth) | Looks up, links, and creates users from OAuth provider data |

## Logic Traits

| Trait | Page | Summary |
|-------|------|---------|
| `QueryBuilder` | [Logic/QueryBuilder →](./logic/QueryBuilder) | Paginated listing, single-record lookup, multi-ID fetching, and flat-list helpers |
| `MethodTransformers` | [Logic/MethodTransformers →](./logic/MethodTransformers) | Lifecycle hook dispatcher — fans out to every `{hookName}{Trait}` method across loaded traits |
| `Relationships` | [Logic/Relationships →](./logic/Relationships) | Syncs all Eloquent relationship types (BelongsToMany, HasMany, MorphMany, MorphTo, MorphToMany) on save |
| `RelationshipHelpers` | [Logic/RelationshipHelpers →](./logic/RelationshipHelpers) | Reflection-based relationship discovery and foreign key resolution |
| `Schema` | [Logic/Schema →](./logic/Schema) | Active schema management and input chunking helpers |
| `CountBuilders` | [Logic/CountBuilders →](./logic/CountBuilders) | Cached aggregate counts for status tabs (all, published, draft, trash) |
| `Dates` | [Logic/Dates →](./logic/Dates) | Normalises date fields to `Y-m-d H:i:s` before save |
| `DispatchEvents` | [Logic/DispatchEvents →](./logic/DispatchEvents) | Dispatches domain events (create/update/delete/restore) after database commit |
| `CollationSelector` | [Logic/CollationSelector →](./logic/CollationSelector) | Applies explicit MySQL collation to LIKE search queries on text columns |
| `CacheableTrait` | [Logic/CacheableTrait →](./logic/CacheableTrait) | Relationship-aware caching for index and record queries |
| `InspectTraits` | [Logic/InspectTraits →](./logic/InspectTraits) | Runtime introspection — checks whether repository or model uses a given trait |
| `TouchableEloquentModel` | [Logic/TouchableEloquentModel →](./logic/TouchableEloquentModel) | Deferred `updated_at` touching — fires exactly once after all relationship syncs |
