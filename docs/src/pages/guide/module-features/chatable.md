---
outline: deep
sidebarPos: 4
---

# Chatable

The Chatable feature adds a chat thread to a model (e.g. tickets, orders). Chat and ChatMessage are managed by dedicated controllers; there is no repository trait for Chatable.

## Entity Trait: Chatable

Add the `Chatable` trait to your model:

```php
<?php

namespace Modules\Ticket\Entities;

use Unusualify\Modularous\Entities\Model;
use Unusualify\Modularous\Entities\Traits\Chatable;

class Ticket extends Model
{
    use Chatable;
}
```

### Relationships

- **chat()** — morphOne to `Chat` (one chat per record)
- **chatMessages()** — hasManyThrough to `ChatMessage` via `Chat`
- **creatorChatMessages()** — chat messages from the record's creator
- **latestChatMessage()** — hasOneThrough to the latest message
- **unreadChatMessages()** — unread messages
- **unreadChatMessagesForYou()** — unread messages not from authorized user
- **unreadChatMessagesFromCreator()** — unread messages from the record creator
- **unreadChatMessagesFromClient()** — unread messages from client

### Appended Attributes

- **chat_messages_count**
- **unread_chat_messages_count**
- **unread_chat_messages_for_you_count**

### Boot Logic

- On **retrieved**: Creates a Chat if none exists
- On **created**: Creates a Chat
- On **saving**: Unsets `_chat_id` (internal use only)

## Repository Trait

There is no repository trait for Chatable. Chat and ChatMessage CRUD is handled by the `admin.chatable` routes and controllers.

## Input Config

Add a chat input to your route in `Config/config.php`:

```php
'routes' => [
    'item' => [
        'inputs' => [
            [
                'type' => 'chat',
                'label' => 'Messages',
                'height' => '40vh',
                'acceptedExtensions' => ['pdf', 'doc', 'docx', 'pages'],
                'max-attachments' => 3,
            ],
        ],
    ],
],
```

## Hydrate: ChatHydrate

`ChatHydrate` transforms the input into `input-chat` schema.

### Requirements

| Key | Default |
|-----|---------|
| default | -1 |
| height | 40vh |
| bodyHeight | 26vh |
| variant | outlined |
| elevation | 0 |
| color | grey-lighten-2 |
| inputVariant | outlined |

### Output

- **type**: `input-chat`
- **name**: `_chat_id`
- **noSubmit**: true
- **creatable**: hidden
- **endpoints**: index, store, show, update, destroy, attachments, pinnedMessage (admin.chatable routes)
- **filepond**: Embedded Filepond schema for message attachments (default: pdf, doc, docx, pages; max 3)
