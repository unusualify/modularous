---
sidebarPos: 6
sidebarTitle: ChatMessage
---

# ChatMessage

**File**: `src/Entities/ChatMessage.php`  
**Namespace**: `Unusualify\Modularity\Entities`  
**Extends**: `Model`  
**Traits**: `HasCreator`, `HasFileponds`, `ChatMessageScopes`

Individual message within a [Chat](./chat). Tracks read/star/pin/sent/received status and supports file attachments via Filepond. Always eager-loads its `creator`.

## Fillable Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `chat_id` | `int` | Parent chat ID |
| `content` | `string` | Message body |
| `is_read` | `bool` | Read status |
| `is_starred` | `bool` | Starred flag |
| `is_pinned` | `bool` | Pinned flag (only one per chat) |
| `is_sent` | `bool` | Sent status |
| `is_received` | `bool` | Received status |
| `edited_at` | `datetime` | When the message was last edited |
| `notified_at` | `datetime` | When a notification was sent |

## Boot Behaviour

When a message is updated with `is_pinned = true`, all other messages in the same chat are unpinned — only one message can be pinned at a time.

## Relationships

### `chat(): BelongsTo`

The parent [Chat](./chat) this message belongs to.

## Accessors

| Accessor | Type | Description |
|----------|------|-------------|
| `user_profile` | `array\|null` | Profile data of the message creator (via `get_user_profile()`) |
| `attachments` | `Collection` | Filepond uploads with role `attachments` |

## Table

Resolved from `modularity.tables.chat_messages`.

## Related

- [Chat](./chat) — parent chat room
- [HasCreator](/system-reference/backend/entity-traits/relationships/has-creator) — tracks who sent the message
