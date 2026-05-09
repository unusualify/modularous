---
sidebarPos: 12
sidebarTitle: Entities
---

# Entities

Modularous entities (models) use traits for feature composition. All models extend `Unusualify\Modularous\Entities\Model`.

For detailed documentation on each model, see the [Entities reference](/system-reference/backend/entities/overview).

## Base Classes

| Class | Purpose |
|-------|---------|
| [**Model**](/system-reference/backend/entities/model) | Base Eloquent model — soft-deletes, tagging, caching, presenter |
| [**Revision**](/system-reference/backend/entities/revision) | Abstract base for revision-tracking models |
| [**Singleton**](/system-reference/backend/entities/singleton) | Singleton pattern for single-record models |

## Core Models

| Model | Purpose |
|-------|---------|
| [User](/system-reference/backend/entities/user) | Authenticatable user with roles, OAuth, API tokens |
| [UserOauth](/system-reference/backend/entities/user-oauth) | OAuth provider link record |
| [Profile](/system-reference/backend/entities/profile) | Extended user profile data |
| [Company](/system-reference/backend/entities/company) | Organisation/company record with billing info |
| [File](/system-reference/backend/entities/file) | Uploaded file record (non-image) |
| [Media](/system-reference/backend/entities/media) | Image record with dimensions, alt text, captions |
| [Filepond](/system-reference/backend/entities/filepond) | Permanent Filepond upload record |
| [TemporaryFilepond](/system-reference/backend/entities/temporary-filepond) | Temporary upload before form submission |
| [Block](/system-reference/backend/entities/block) | Content block with nested children |
| [Repeater](/system-reference/backend/entities/repeater) | Repeatable content via morph relation |
| [Tag](/system-reference/backend/entities/tag) | Tag with locale support |
| [Tagged](/system-reference/backend/entities/tagged) | Taggable pivot record |
| [Process](/system-reference/backend/entities/process) | State-machine workflow instance |
| [ProcessHistory](/system-reference/backend/entities/process-history) | Process status change audit trail |
| [Assignment](/system-reference/backend/entities/assignment) | Task assignment with status and due dates |
| [Authorization](/system-reference/backend/entities/authorization) | Authorizable relationship pivot |
| [CreatorRecord](/system-reference/backend/entities/creator-record) | Creator tracking record |
| [State](/system-reference/backend/entities/state) | Translatable state definition |
| [Stateable](/system-reference/backend/entities/stateable) | Morph pivot linking state to model |
| [Spread](/system-reference/backend/entities/spread) | Dynamic JSON data via morph relation |
| [Setting](/system-reference/backend/entities/setting) | Key-value settings with translations |
| [Chat](/system-reference/backend/entities/chat) | Chat room attached via morph relation |
| [ChatMessage](/system-reference/backend/entities/chat-message) | Individual chat message |
| [Feature](/system-reference/backend/entities/feature) | Featured/starred content for buckets |
| [RelatedItem](/system-reference/backend/entities/related-item) | Polymorphic related content pivot |
| [NestedsetCollection](/system-reference/backend/entities/nestedset-collection) | Extended nested-set tree collection |

## Entity Traits

### Core

| Trait | Purpose |
|-------|---------|
| HasCaching | Cache support |
| HasCacheDependents | Cache invalidation |
| HasCompany | Company scoping |
| HasScopes | Query scopes |
| ChangeRelationships | Relationship helpers |
| LocaleTags | Locale tag casting |
| ModelHelpers | General helpers |

### Auth

| Trait | Purpose |
|-------|---------|
| HasOauth | OAuth integration |
| CanRegister | Registration support |

### Features

| Trait | Purpose |
|-------|---------|
| HasImages | Image/media relationship |
| HasFiles | File relationship |
| HasFileponds | Filepond relationship |
| HasSlug | Slug generation |
| HasStateable | State workflow |
| HasPriceable | Pricing |
| HasPayment | Payment integration |
| HasPosition | Ordering |
| HasPresenter | Presenter pattern |
| HasCreator | Creator tracking |
| HasRepeaters | Repeater fields |
| HasProcesses | Process workflow |
| HasSpreadable | Spread feature |
| HasUuid | UUID primary key |
| HasTranslation | Translation |
| IsTranslatable | Translatable model |
| IsSingular | Singleton behavior |
| IsHostable | Multi-tenant |
| HasAuthorizable | Authorization |

### Other

| Trait | Purpose |
|-------|---------|
| Assignable | Assignment target |
| Chatable | Chat support |
| Processable | Process participant |
| HasBlocks | Block content |
| HasNesting | Nested structure |
| HasRelated | Related items |
| HasRevisions | Revision history |

## Enums

| Enum | Purpose |
|------|---------|
| Permission | Permission types |
| UserRole | User roles |
| RoleTeam | Role team (Cms, Crm, Erp) |
| ProcessStatus | Process workflow status |
| PaymentStatus | Payment status |
| AssignmentStatus | Assignment status |

## Scopes

StateableScopes, SingularScope, ProcessableScopes, ProcessScopes, ChatableScopes, ChatMessageScopes, AssignmentScopes, AssignableScopes
