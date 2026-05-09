---
sidebarPos: 1
sidebarTitle: Overview
---

# Entity Traits

Modularous ships a comprehensive set of Eloquent model traits organized into four groups. Mix and match them on your models to compose the exact feature set needed.

## Trait Groups

| Group | Namespace | Purpose |
|-------|-----------|---------|
| [Top-Level](#top-level-traits) | `Unusualify\Modularous\Entities\Traits\` | Core domain behaviors — relationships, media, state, slugs, etc. |
| [Core](#core-traits) | `Unusualify\Modularous\Entities\Traits\Core\` | Low-level plumbing — caching, scopes, change tracking, locale tags |
| [Auth](#auth-traits) | `Unusualify\Modularous\Entities\Traits\Auth\` | Authentication helpers — OAuth linking, email-verification registration |
| [Secondary](#secondary-traits) | `Unusualify\Modularous\Entities\Traits\Secondary\` | Optional extras — nesting, blocks, revisions, related items |

---

## Top-Level Traits

| Trait | Page | Summary |
|-------|------|---------|
| `Assignable` | [Assignable](./relationships/assignable) | User/role assignment with `assignments()` morph relation |
| `Chatable` | [Chatable](./relationships/chatable) | Auto-created Chat thread + messages per model |
| `HasAuthorizable` | [HasAuthorizable](./relationships/has-authorizable) | Per-record authorization (`Authorization` morph) |
| `HasCreator` | [HasCreator](./relationships/has-creator) | Tracks the User who created the record |
| `HasFileponds` | [HasFileponds](./media/has-fileponds) | Filepond temp-file tracking with collection management |
| `HasFiles` | [HasFiles](./media/has-files) | File attachments via `MorphToMany` to `File` |
| `HasImages` | [HasImages](./media/has-images) | Media library attachments with crop/param helpers |
| `HasPayment` | [HasPayment](./payment/has-payment) | Payment/price state with full status helpers |
| `HasPriceable` | [HasPriceable](./payment/has-priceable) | Base pricing with currency exchange support |
| `HasPosition` | [HasPosition](./model-behavior/has-position) | Auto-position assignment and `setNewOrder()` |
| `HasPresenter` | [HasPresenter](./model-behavior/has-presenter) | Presenter pattern (`present()`, `presentAdmin()`) |
| `HasProcesses` | [HasProcesses](./processes/has-processes) | Approval workflow processes via morph relation |
| `HasRepeaters` | [HasRepeaters](./repeaters/has-repeaters) | Nested repeater blocks (media + filepond + pricing) |
| `HasScopes` *(deprecated)* | [Deprecated →](./deprecated) | Alias for `Core\HasScopes` |
| `HasSlug` | [HasSlug](./model-behavior/has-slug) | Slug generation and `resolveRouteBinding()` via slug |
| `HasSpreadable` | [HasSpreadable](./model-behavior/has-spreadable) | JSON spread attributes as dynamic model properties |
| `HasStateable` | [HasStateable](./model-behavior/has-stateable) | State machine with event dispatch |
| `HasTranslation` | [HasTranslation](./translation/has-translation) | Multi-locale content via `astrotomic/laravel-translatable` |
| `HasUuid` | [HasUuid](./model-behavior/has-uuid) | Auto UUID primary key (`ordered_uuid`) |
| `IsHostable` | [IsHostable](./singletons/is-hostable) | Slug + hostable route resolution across parent hierarchy |
| `IsSingular` | [IsSingular](./singletons/is-singular) | Singleton model stored in shared `modularous_singletons` table |
| `IsTranslatable` | [IsTranslatable](./translation/is-translatable) | Check helper — detects if model uses translations |
| `ModelHelpers` *(deprecated)* | [Deprecated →](./deprecated) | Alias for `Core\ModelHelpers` |
| `Processable` | [Processable](./processes/processable) | Single-process workflow: confirm / reject flow |

---

## Core Traits

| Trait | Page | Summary |
|-------|------|---------|
| `Core\ModelHelpers` | [ModelHelpers](./core/model-helpers) | Composes scopes, routes, activity logging, title helpers |
| `Core\HasScopes` | [HasScopes](./core/has-scopes) | `published`, `visible`, `draft`, global scope wiring |
| `Core\HasCaching` | [HasCaching](./core/has-caching) | Auto cache invalidation via `CacheObserver` |
| `Core\HasCacheDependents` | [HasCacheDependents](./core/has-cache-dependents) | Cross-model cache dependency graph |
| `Core\HasCompany` | [HasCompany](./core/has-company) | Company association with auto-create on save |
| `Core\ChangeRelationships` | [ChangeRelationships](./core/change-relationships) | Tracks which relationships changed during a request |
| `Core\LocaleTags` | [LocaleTags](./core/locale-tags) | Locale-scoped tagging (`tagLocale`, `untagLocale`) |

---

## Auth Traits

| Trait | Page | Summary |
|-------|------|---------|
| `Auth\CanRegister` | [CanRegister](./auth/can-register) | Email-verification token dispatch for registration flow |
| `Auth\HasOauth` | [HasOauth](./auth/has-oauth) | OAuth provider linking (`UserOauth` has-many) |

---

## Secondary Traits

| Trait | Page | Summary |
|-------|------|---------|
| `Secondary\HasBlocks` | [HasBlocks](./secondary/has-blocks) | Content blocks (morph-many, ordered, rendered) |
| `Secondary\HasNesting` | [HasNesting](./secondary/has-nesting) | Nested-set slug traversal and tree save |
| `Secondary\HasRelated` | [HasRelated](./secondary/has-related) | Related-item linking via morph-many pivot |
| `Secondary\HasRelation` | [HasRelation](./secondary/has-relation) | Minimal stub — forceDeleting hook placeholder |
| `Secondary\HasRevisions` | [HasRevisions](./secondary/has-revisions) | Revision history (has-many, descending) |
