---
sidebarPos: 2
sidebarTitle: Chatable
---

# Chatable

**Namespace**: `Unusualify\Modularous\Entities\Traits\Chatable`

Gives every model its own `Chat` thread with full message history, read status tracking, unread counts, and notification dispatch. Uses `ChatableScopes` for query filtering.

---

## Boot Behavior

| Event | Action |
|-------|--------|
| `retrieved` | Sets `_chat_id` attribute if chat exists; creates a `Chat` record if the model is persisted and has no chat |
| `created` | Creates the initial `Chat` record |
| `saving` | Removes `_chat_id` from dirty attributes |

---

## Relationships

```php
public function chat(): MorphOne                          // → Chat (auto-created)
public function chatMessages(): HasManyThrough            // → ChatMessage through Chat
public function creatorChatMessages(): HasManyThrough     // → ChatMessages sent by the model's creator
public function latestChatMessage(): HasOneThrough        // → Single most recent ChatMessage
public function unreadChatMessages(): HasManyThrough      // → Unread messages (is_read = false)
public function unreadChatMessagesForYou(): HasManyThrough // → Unread messages not authored by current user
public function unreadChatMessagesFromClient(): HasManyThrough
public function unreadChatMessagesFromCreator(): HasManyThrough
```

---

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `truncateChat` | `(): void` | Deletes and recreates the chat thread (clears all messages) |
| `handleChatableNotification` | `(): void` | Dispatches an `UnreadChatMessage` event and sends a `ChatableUnreadNotification` if the latest message is unread and has been waiting longer than `$chatableNotificationInterval` minutes |
| `numberOfChatMessages` | `(): int` | Count of all chat messages |
| `numberOfUnreadChatMessages` | `(): int` | Count of unread messages |
| `numberOfUnreadChatMessagesForYou` | `(): int` | Count of unread messages not authored by current user |
| `numberOfUnreadChatMessagesFromCreator` | `(): int` | Count of unread messages from the record's creator |
| `numberOfUnreadChatMessagesFromClient` | `(): int` | Count of unread messages from client-role users |
| `numberOfUnansweredCreatorChatMessages` | `(): int` | Returns `1` if the latest message is from the creator and unread, else `0` |

---

## Computed Attributes

| Attribute | Description |
|-----------|-------------|
| `chat_messages_count` | Total message count |
| `unread_chat_messages_count` | Unread message count |
| `unread_chat_messages_for_you_count` | Unread messages not from current user |
| `unread_chat_messages_from_creator_count` | Unread messages from the record's creator |

---

## Configuration

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$chatableNotificationInterval` | `int` | `60` | Minutes before a notification is re-dispatched for an unanswered message |

---

## Usage

```php
use Unusualify\Modularous\Entities\Traits\Chatable;

class Order extends Model
{
    use Chatable;
}

// Chat record (auto-created)
$order->chat;

// Messages
$order->chatMessages()->latest()->get();
$order->latestChatMessage;
$order->unreadChatMessages()->count();

// Counts
$order->numberOfUnreadChatMessages();
$order->chat_messages_count;

// Clear chat history
$order->truncateChat();

// Dispatch notification if needed
$order->handleChatableNotification();
```
