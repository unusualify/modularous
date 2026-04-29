---
sidebarPos: 3
sidebarTitle: Chat Message
---

# ChatMessage

`ChatMessage` renders a single chat message bubble. It supports outgoing/incoming layout, read/unread blur state, content truncation with expand/collapse, attachment previews, star and pin actions, and relative timestamp display.

## Usage

```html
<chat-message
  v-model="message"
  update-endpoint="/api/messages/:id"
  :reverse="message.user_id === currentUser.id"
  :content-truncate-length="300"
  no-pinning
/>
```

## Props

| Prop | Type | Default | Description |
|---|---|---|---|
| `modelValue` | `Object` | required | The message object. See [Message shape](#message-shape). |
| `updateEndpoint` | `String` | required | URL template for PATCH/PUT requests. `:id` is replaced with `message.id`. |
| `reverse` | `Boolean` | `false` | When `true`, renders the bubble on the right side (outgoing message style). |
| `avatarSize` | `Number` | `40` | Avatar diameter in px on desktop (`smAndUp`). |
| `mobileAvatarSize` | `Number` | `20` | Avatar diameter in px on mobile. |
| `contentTruncateLength` | `Number` | `50` | Character threshold above which a **Show more / Show less** toggle is displayed. |
| `noStarring` | `Boolean` | `false` | Hides the star icon. |
| `noPinning` | `Boolean` | `false` | Hides the pin icon. |

## Message shape

```js
{
  id: 1,
  content: 'Hello world',
  created_at: '2025-04-17T10:00:00Z',
  is_read: false,
  is_starred: false,
  is_pinned: false,
  attachments: [],          // array of file objects (forwarded to ue-filepond-preview)
  user_profile: {
    name: 'Jane Doe',
    avatar_url: 'https://...',
  },
}
```

## Read / unread state

A message is considered **unread** when `message.is_read === false` **and** `reverse === false` (i.e. it is an incoming, not yet read message).

Unread messages:
- Have `.message-content--unread` — content is blurred (`filter: blur(2px)`) until the user hovers.
- On hover, a 1-second timer starts. If the user stays, `markAsRead()` fires a `PUT` to `updateEndpoint` with `{ is_read: true }` and the blur clears.

## Timestamp display

| Time since sent | Format |
|---|---|
| < 48 hours | Relative (`moment.fromNow()`) |
| 2 – 7 days | Day name (`dddd`) |
| > 7 days | `MMM Do YY` |

On desktop (`smAndUp`) the timestamp is displayed as absolute-positioned text in the bottom corner of the bubble. On mobile it is accessible via a `v-tooltip` on the avatar.

## Star & pin actions

Clicking the star or pin icon immediately calls `PUT updateEndpoint` with `{ is_starred: Boolean }` or `{ is_pinned: Boolean }`. On success the local model is updated optimistically.

## Content truncation

When `message.content.length > contentTruncateLength`:
- Only the first `contentTruncateLength` characters are shown, followed by `...`
- A **Show more** / **Show less** toggle button expands/collapses the full content in a `v-expand-transition`

## Attachments

When `message.attachments.length > 0`, a labelled `ue-filepond-preview` list is rendered below the message body.

## Emits

| Event | Payload | Description |
|---|---|---|
| `update:modelValue` | `Object` | Emitted after a successful `is_starred`, `is_pinned`, or `is_read` update |
