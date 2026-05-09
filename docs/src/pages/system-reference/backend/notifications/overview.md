---
sidebarPos: 15
sidebarTitle: Overview
sidebarGroupTitle: Notifications
---

# Notifications

Modularous ships two tiers of notifications that serve different purposes.

| Tier | Namespace | Purpose |
|------|-----------|---------|
| **Auth notifications** | `Unusualify\Modularous\Notifications\` | Low-level transactional emails for registration and password flows |
| **System notifications** | `Modules\SystemNotification\Notifications\` | Feature-rich, queue-able, multi-channel notifications tied to model lifecycle events |

---

## Tier 1 — Auth Notifications

Three standalone notification classes in `src/Notifications/`:

| Class | Triggered by | Email subject |
|-------|-------------|---------------|
| [EmailVerification](./auth-notifications#emailverification) | Registration with email verification | "Email Verification" |
| [GeneratePasswordNotification](./auth-notifications#generatepasswordnotification) | New account with generated password | "Generate Your Password For New Account" |
| [ResetPasswordNotification](./auth-notifications#resetpasswordnotification) | Forgot-password flow | "Reset your {app} password" |

These extend Laravel's `Notification` directly, deliver only via `mail`, and are **not queued** by default.

→ [Auth Notifications reference](./auth-notifications)

---

## Tier 2 — System Notifications

Fourteen notification classes in the `SystemNotification` module, all extending `FeatureNotification`:

| Class | Triggered by | Default channels |
|-------|-------------|-----------------|
| [ModelCreatedNotification](./system-notifications#model-lifecycle) | `ModelCreated` event | `mail` |
| [ModelUpdatedNotification](./system-notifications#model-lifecycle) | `ModelUpdated` event | `mail` |
| [ModelDeletedNotification](./system-notifications#model-lifecycle) | `ModelDeleted` event | `mail` |
| [ModelRestoredNotification](./system-notifications#model-lifecycle) | `ModelRestored` event | `mail` |
| [ModelForceDeletedNotification](./system-notifications#model-lifecycle) | `ModelForceDeleted` event | `mail` |
| [StateableUpdatedNotification](./system-notifications#stateable) | `StateableUpdated` event | configurable |
| [TaskCreatedNotification](./system-notifications#tasks) | `AssignmentCreated` event | configurable |
| [TaskUpdatedNotification](./system-notifications#tasks) | `AssignmentUpdated` event | configurable |
| [TaskAssignedToAuthorizableNotification](./system-notifications#tasks) | `AssignmentCreated` event | `database,mail` |
| [PaymentCompletedNotification](./system-notifications#payment) | `PaymentCompleted` event | configurable |
| [PaymentFailedNotification](./system-notifications#payment) | `PaymentFailed` event | `database,mail` |
| [ChatableUnreadNotification](./system-notifications#chat) | `UnreadChatMessage` event | configurable |
| [FeatureNotification](./feature-notification) | Abstract base — not dispatched directly | — |
| [LogNotification](./system-notifications#log) | Monolog handler | `mail` |

→ [FeatureNotification base class](./feature-notification)  
→ [System Notifications reference](./system-notifications)

---

## Notification Model

The `SystemNotification` module stores database-channel notifications in a `notifications` table via the `Notification` Eloquent model.

```
notifications table
├── id            (UUID string, non-incrementing)
├── type          (fully-qualified notification class name)
├── notifiable_type / notifiable_id  (polymorphic)
├── data          (JSON — subject, message, htmlMessage, redirector, …)
└── read_at       (nullable datetime)
```

Appended attributes on the model: `is_read`, `is_mine`, `subject`, `message`, `html_message`, `redirector`, `has_redirector`, `redirector_text`.

---

## Flow: Event → Listener → Notification

```
[Model saved / state changed / payment event]
        │
        ▼
  SystemNotification Event dispatched
  (e.g. ModelUpdated, StateableUpdated)
        │
        ▼
  Listener::handle($event)
  (e.g. ModelListener, StateableListener)
        │
        ├─► notification->notify(...)  [database channel]
        │       stored in notifications table
        │
        └─► Notification::route('mail', ...)  [mail channel]
                queued via modularous.notifications.mail_queue
```

---

## Configuration

All system-notification behaviour is governed by `config/modularous.php` under the `notifications` key:

| Key | Description |
|-----|-------------|
| `modularous.mail.enabled` | Master switch — disables all mail from `Listener::handle()` when `false` |
| `modularous.notifications.mail_connection` | Queue connection used for mail channel |
| `modularous.notifications.database_connection` | Queue connection used for database channel |
| `modularous.notifications.mail_queue` | Queue name for mail jobs |
| `modularous.notifications.database_queue` | Queue name for database notification jobs |
| `modularous.notifications.{ClassName}.channels` | Per-class channel override (comma-separated string) |
| `modularous.notifications.authorizable.channels` | Channel override for `TaskAssignedToAuthorizableNotification` |
