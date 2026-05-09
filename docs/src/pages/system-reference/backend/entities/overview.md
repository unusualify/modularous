---
sidebarPos: 1
sidebarTitle: Entities Overview
---

# Entities

**Namespace**: `Unusualify\Modularous\Entities`  
**Location**: `src/Entities/`

All Eloquent models in the Modularous package. Module-generated models extend `Model` and gain soft-deletes, tagging, caching, presenter support, and trait composition out of the box.

## Base Classes

| Class | File | Purpose |
|-------|------|---------|
| [Model](./model) | `Model.php` | Abstract base — soft-deletes, tagging, caching, presenter, notifications |
| [Revision](./revision) | `Revision.php` | Abstract base for revision-tracking models |

## Auth & User

| Class | File | Purpose |
|-------|------|---------|
| [User](./user) | `User.php` | Authenticatable user with roles, OAuth, company, API tokens |
| [UserOauth](./user-oauth) | `UserOauth.php` | OAuth provider link record for a user |
| [Profile](./profile) | `Profile.php` | Extended user profile data |
| [Company](./company) | `Company.php` | Organisation/company record with billing info |

## Media & Files

| Class | File | Purpose |
|-------|------|---------|
| [File](./file) | `File.php` | Uploaded file record (non-image) |
| [Media](./media) | `Media.php` | Image record with dimensions, alt text, captions |
| [Filepond](./filepond) | `Filepond.php` | Permanent Filepond upload record (morph relation) |
| [TemporaryFilepond](./temporary-filepond) | `TemporaryFilepond.php` | Temporary upload before form submission |

## Content Building Blocks

| Class | File | Purpose |
|-------|------|---------|
| [Block](./block) | `Block.php` | Content block with nested children and media |
| [Repeater](./repeater) | `Repeater.php` | Repeatable content attached via morph relation |
| [Tag](./tag) | `Tag.php` | Tag record with locale support |
| [Tagged](./tagged) | `Tagged.php` | Taggable pivot record |

## Process & Workflow

| Class | File | Purpose |
|-------|------|---------|
| [Process](./process) | `Process.php` | State-machine workflow instance |
| [ProcessHistory](./process-history) | `ProcessHistory.php` | Audit trail of process status changes |
| [Assignment](./assignment) | `Assignment.php` | Task assignment with status, due date, file attachments |
| [Authorization](./authorization) | `Authorization.php` | Authorizable relationship pivot |
| [CreatorRecord](./creator-record) | `CreatorRecord.php` | Tracks who created a model instance |

## State & Data

| Class | File | Purpose |
|-------|------|---------|
| [State](./state) | `State.php` | Translatable state definition (e.g. Draft, Active, Closed) |
| [Stateable](./stateable) | `Stateable.php` | Morph pivot linking a state to a model |
| [Spread](./spread) | `Spread.php` | Dynamic key-value JSON data attached via morph relation |
| [Setting](./setting) | `Setting.php` | Key-value settings with translations and images |
| [Singleton](./singleton) | `Singleton.php` | Single-record model for unique data |

## Communication

| Class | File | Purpose |
|-------|------|---------|
| [Chat](./chat) | `Chat.php` | Chat room attached to a model via morph relation |
| [ChatMessage](./chat-message) | `ChatMessage.php` | Individual message with read/pin/star status |

## Other

| Class | File | Purpose |
|-------|------|---------|
| [Feature](./feature) | `Feature.php` | Featured/starred content for bucket-based curation |
| [RelatedItem](./related-item) | `RelatedItem.php` | Many-to-many polymorphic related content |
| [NestedsetCollection](./nestedset-collection) | `NestedsetCollection.php` | Extended nested-set tree collection |
