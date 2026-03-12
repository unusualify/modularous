---
sidebarPos: 12
sidebarTitle: Entities
---

# Entities

Modularity entities (models) use traits for feature composition. All models extend `Unusualify\Modularity\Models\Model`.

## Base Classes

| Class | Purpose |
|-------|---------|
| **Model** | Base Eloquent model |
| **Singleton** | Singleton pattern for single-record models |

## Core Models

User, UserOauth, Profile, Company, Setting, Tag, Tagged, Media, File, Filepond, TemporaryFilepond, Block, Repeater, RelatedItem, Revision, Process, ProcessHistory, Chat, ChatMessage, Assignment, Authorization, CreatorRecord, Feature, State, Stateable, Spread

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
