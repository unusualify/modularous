---
sidebarPos: 5
sidebarTitle: Chat
---

# Chat

**File**: `src/Entities/Chat.php`  
**Namespace**: `Unusualify\Modularity\Entities`  
**Extends**: `Model`

Chat room attached to a parent model via a polymorphic relationship. A chat contains ordered messages, supports pinned messages, and exposes file attachments collected from its messages.

## Fillable Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `chatable_id` | `int` | Parent model ID |
| `chatable_type` | `string` | Parent model class |

## Relationships

### `chatable(): MorphTo`

The parent model this chat is associated with.

### `messages(): HasMany`

All [ChatMessage](./chat-message) records in this chat.

### `latestMessage(): HasOne`

The most recent message (using `latestOfMany('created_at')`).

### `fileponds(): HasManyThrough`

All Filepond uploads through messages in this chat.

## Accessors

| Accessor | Type | Description |
|----------|------|-------------|
| `attachments` | `Collection` | All fileponds with role `attachments`, formatted via `mediableFormat()` |
| `pinned_message` | `ChatMessage\|null` | The currently pinned message |

## Table

Resolved from `modularity.tables.chats`.

## Related

- [ChatMessage](./chat-message) — individual messages
- [ChatController](/system-reference/backend/http/controllers/chat-controller) — chat API endpoints
- [Chatable](/system-reference/backend/entity-traits/relationships/chatable) — trait that adds chat support to models
