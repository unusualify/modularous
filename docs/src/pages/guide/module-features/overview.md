---
sidebarPos: 1
sidebarTitle: Overview
---

# Module Features Overview

Modularous module features follow a **triple pattern**: Entity trait + Repository trait + Hydrate. Each layer handles a specific concern.

See [Features Pattern](/system-reference/features) for the full pattern explanation. For generics (Allowable, Relationships, Files and Media, etc.), see [Generics](/guide/generics/overview).

| Layer | Location | Purpose |
|-------|----------|---------|
| **Entity trait** | `Entities/Traits/Has*.php` | Model relationships, boot logic, accessors |
| **Repository trait** | `Repositories/Traits/*Trait.php` | Persistence: hydrate, afterSave, getFormFields |
| **Hydrate** | `Hydrates/Inputs/*Hydrate.php` | Schema transformation for form input |

## Feature Matrix

| Feature | Config type | Entity Trait | Repository Trait | Hydrate | Output type |
|---------|-------------|--------------|------------------|---------|-------------|
| [Media/Images](#media--images) | `image` | HasImages | ImagesTrait | ImageHydrate | input-image |
| [Files](#files) | `file` | HasFiles | FilesTrait | FileHydrate | input-file |
| [Filepond](#filepond) | `filepond` | HasFileponds | FilepondsTrait | FilepondHydrate | input-filepond |
| [Spread](#spread) | `spread` | HasSpreadable | SpreadableTrait | SpreadHydrate | input-spread |
| [Slug](#slug) | — | HasSlug | SlugsTrait | — | — |
| [Authorizable](#authorizable) | `authorize` | HasAuthorizable | AuthorizableTrait | AuthorizeHydrate | select |
| [Creator](#creator) | `creator` | HasCreator | CreatorTrait | CreatorHydrate | input-browser |
| [Payment](#payment) | `price`, `payment-service` | HasPayment | PaymentTrait | — | — |
| [Priceable](#priceable) | `price` | HasPriceable | PricesTrait | PriceHydrate | input-price |
| [Position](#position) | — | HasPosition | — | — | — |
| [Repeaters](#repeaters) | `repeater` | HasRepeaters | RepeatersTrait | RepeaterHydrate | input-repeater |
| [Singular](#singular) | — | IsSingular | — | — | — |
| [Stateable](#stateable) | `stateable` | HasStateable | StateableTrait | StateableHydrate | select |
| [Processable](#processable) | `process` | Processable | ProcessableTrait | ProcessHydrate | input-process |
| [Chatable](#chatable) | `chat` | Chatable | — | ChatHydrate | input-chat |
| [Assignable](#assignable) | `assignment` | Assignable | AssignmentTrait | AssignmentHydrate | input-assignment |
| [Translation](#translation) | `translated: true` | IsTranslatable, HasTranslation | TranslationsTrait | — | — |

---

## Media / Images

**Entity**: `HasImages` — morphToMany with Media; role-based, locale-aware; `media()`, `findMedia()`, `image()`, `imagesList()`.

**Repository**: `ImagesTrait` — `setColumnsImagesTrait`, `hydrateImagesTrait`, `afterSaveImagesTrait`, `getFormFieldsImagesTrait`.

**Hydrate**: `ImageHydrate` — type → `input-image`, default name `images`.

---

## Files

**Entity**: `HasFiles` — morphToMany with File; role/locale pivot; `files()`, `file()`, `filesList()`, `fileObject()`.

**Repository**: `FilesTrait` — `setColumnsFilesTrait`, `hydrateFilesTrait`, `afterSaveFilesTrait`, `getFormFieldsFilesTrait`. Syncs pivot via `file_id`, `role`, `locale`.

**Hydrate**: `FileHydrate` — type → `input-file`, default name `files`.

---

## Filepond

**Entity**: `HasFileponds` — morphMany Filepond; `fileponds()`, `getFileponds()`, `hasFilepond()`. One-to-many direct binding (no file library).

**Repository**: `FilepondsTrait` — Handles filepond sync, temp file conversion.

**Hydrate**: `FilepondHydrate` — type → `input-filepond`; sets `endPoints` (process, revert, load), `max-files`, `accepted-file-types`, labels.

---

## Spread

**Entity**: `HasSpreadable` — Stores flexible JSON in a Spread model; `getSpreadableSavingKey()`, `spreadable()`.

**Repository**: `SpreadableTrait` — Persists spread payload.

**Hydrate**: `SpreadHydrate` — type → `input-spread`; `reservedKeys` from route inputs; `name` from `getSpreadableSavingKey()`.

---

## Slug

**Entity**: `HasSlug` — hasMany Slug; `slugs()`, `getSlugClass()`, `setSlugs()`. URL slugs per locale.

**Repository**: `SlugsTrait` — Slug persistence.

**Hydrate**: None (slug is derived from route/translatable fields).

---

## Authorizable

**Entity**: `HasAuthorizable` — morphOne Authorization; `authorizationRecord()`, `authorized_id`, `authorized_type`. Assigns an authorized model (e.g. User).

**Repository**: `AuthorizableTrait` — Syncs authorization record.

**Hydrate**: `AuthorizeHydrate` — type → `select`; `name` = `authorized_id`; resolves `authorized_type` from model; `scopeRole` filters by Spatie role.

---

## Creator

**Entity**: `HasCreator` — morphOne CreatorRecord; `creator()`, `custom_creator_id`. Tracks who created the record.

**Repository**: `CreatorTrait` — `setColumnsCreatorTrait`, `hydrateCreatorTrait`, `afterSaveCreatorTrait`.

**Hydrate**: `CreatorHydrate` — type → `input-browser`; `name` = `custom_creator_id`; endpoint → `admin.system.user.index`; `allowedRoles` (e.g. superadmin).

---

## Payment

**Entity**: `HasPayment` — uses `HasPriceable`; links to SystemPayment module; `paymentPrice`, `paidPrices`; `PaymentStatus` enum.

**Repository**: `PaymentTrait` — Payment-related persistence.

**Hydrate**: None (uses PriceHydrate for price inputs). `PaymentServiceHydrate` for payment service selection.

---

## Priceable

**Entity**: `HasPriceable` — morphMany Price (SystemPricing); `prices()`, `basePrice()`, `originalBasePrice()`; language-based pricing via CurrencyExchange.

**Repository**: `PricesTrait` — Price sync.

**Hydrate**: `PriceHydrate` — type → `input-price`; `items` from CurrencyProvider; optional `vatRates`; `hasVatRate`.

---

## Position

**Entity**: `HasPosition` — `position` column; `scopeOrdered()`; `setNewOrder($ids)` for reordering. Auto-sets last position on create.

**Repository**: None (column-only).

**Hydrate**: None.

---

## Repeaters

**Entity**: `HasRepeaters` — uses HasFiles, HasImages, HasPriceable, HasFileponds; morphMany Repeater; `repeaters($role, $locale)`.

**Repository**: `RepeatersTrait` — Repeater CRUD, schema resolution.

**Hydrate**: `RepeaterHydrate` — type → `input-repeater`; `schema` for nested inputs; `root`, `draggable`, `orderKey`.

---

## Singular

**Entity**: `IsSingular` — Global scope `SingularScope`; single record per type; `singleton_type`, `content` JSON; fillable stored in `content`.

**Repository**: None (singleton pattern).

**Hydrate**: None.

---

## Stateable

**Entity**: `HasStateable` — morphOne State; `stateable()`, `stateable_status`; workflow states.

**Repository**: `StateableTrait` — State sync; `getStateableList()`.

**Hydrate**: `StateableHydrate` — type → `select`; `name` = `stateable_id`; `items` from repository `getStateableList()`.

---

## Processable

**Entity**: `Processable` — morphOne Process; `process()`, `processHistories()`; `ProcessStatus` enum; `setProcessStatus()`.

**Repository**: `ProcessableTrait` — Process lifecycle.

**Hydrate**: `ProcessHydrate` — type → `input-process`; requires `_moduleName`, `_routeName`; `fetchEndpoint`, `updateEndpoint` for process UI.

---

## Chatable

**Entity**: `Chatable` — morphOne Chat; `chat()`, `chatMessages()`; auto-creates Chat on create; appends `chat_messages_count`, `unread_chat_messages_count`.

**Repository**: None (Chat/ChatMessage handled by dedicated controllers).

**Hydrate**: `ChatHydrate` — type → `input-chat`; `endpoints` (index, store, show, update, destroy, attachments, pinnedMessage); embeds Filepond for attachments.

---

## Assignable

**Entity**: `Assignable` — morphMany Assignment; `assignments()`, `activeAssignment()`; `AssignableScopes`; appends `active_assignee_name`.

**Repository**: `AssignmentTrait` — Assignment CRUD.

**Hydrate**: `AssignmentHydrate` — type → `input-assignment`; `assigneeType`, `assignableType`; `fetchEndpoint`, `saveEndpoint`; embeds Filepond for attachments.

---

## Translation

**Entity**: `IsTranslatable`, `HasTranslation` — Astrotomic Translatable; `translations` relation; `translatedAttributes`.

**Repository**: `TranslationsTrait` — `setColumnsTranslationsTrait`, `prepareFieldsBeforeSaveTranslationsTrait`, `getFormFieldsTranslationsTrait`, `filterTranslationsTrait`.

**Hydrate**: None (each input Hydrate respects `translated: true` for locale-aware handling).
