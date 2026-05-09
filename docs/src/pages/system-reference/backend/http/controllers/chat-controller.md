---
sidebarPos: 5
sidebarTitle: ChatController
---

# ChatController

**File**: `src/Http/Controllers/ChatController.php`  
**Namespace**: `Unusualify\Modularous\Http\Controllers`  
**Extends**: `App\Http\Controllers\Controller`

Handles chat messages, attachments, and pinned messages for a `Chat` model instance. Supports both paginated fetching and time-based queries for real-time catch-up.

## Methods

### `index(Request $request, Chat $chat): JsonResponse`

Returns messages for a chat room.

- When `from` is present in the request, returns all messages newer than that timestamp (real-time catch-up). User's own messages are excluded from this query.
- When `from` is absent, returns a paginated list of all messages ordered newest-first.

**Request parameters**:

| Parameter | Type | Description |
|-----------|------|-------------|
| `from` | `datetime` | Only return messages newer than this timestamp |

### `store(Request $request, Chat $chat): JsonResponse`

Creates a new chat message. Accepts an optional `attachments` field (Filepond temporary file keys). The parent `Chat` model is touched (updated_at refreshed) after the message is stored.

### `attachments(Request $request, Chat $chat): JsonResponse`

Returns all file attachments for a chat room, ordered by upload date.

### `update(Request $request, $id): JsonResponse`

Updates the body of an existing message. Touches the parent `Chat` model's timestamp.

### `pinnedMessage(Request $request, $id): JsonResponse`

Returns the currently pinned message for the chat identified by `$id`.

### `destroy(Request $request, ChatMessage $message): JsonResponse`

Deletes a chat message. Intended for message owners or admins.

## Filepond Attachments

Attachments are uploaded via Filepond before the message is created. The `store` action resolves each Filepond temporary key to a permanent `File` record and associates it with the message.

## Related

- [FilepondController](./filepond-controller) — handles temporary Filepond uploads
- [FileLibraryController](./file-library-controller) — permanent file management
