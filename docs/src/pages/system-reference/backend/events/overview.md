---
sidebarPos: 1
sidebarTitle: Overview
sidebarGroupTitle: Events & Listeners
---

# Events & Listeners

Modularous ships a set of events that fire at key points in the application lifecycle. Listeners react to those events and, when mail is enabled, automatically resolve and dispatch the matching notification class.

## Events

| Class | Namespace | Fired When |
|-------|-----------|------------|
| [ModelEvent](./model-event) | `Unusualify\Modularous\Events` | Abstract base — extended by all model-level events |
| [ModularousUserRegistering](./user-events#modularoususerregistering) | `Unusualify\Modularous\Events` | Just before a new user is persisted |
| [ModularousUserRegistered](./user-events#modularoususerregistered) | `Unusualify\Modularous\Events` | Immediately after a user is created |
| [ModularousUserVerification](./user-events#modularoususerverification) | `Unusualify\Modularous\Events` | When an email-verification request is initiated |
| [VerifiedEmailRegister](./user-events#verifiedemailregister) | `Unusualify\Modularous\Events` | When a user completes registration via verified e-mail |

## Listeners

| Class | Namespace | Listens To |
|-------|-----------|------------|
| [Listener](./listener) | `Unusualify\Modularous\Listeners` | Abstract base — extended by concrete listeners |

## Event Traits

All `ModelEvent` subclasses automatically gain the following traits:

| Trait | What it captures |
|-------|-----------------|
| `EventChanges` | Changed model attributes and relationships |
| `EventStateable` | Previous / current stateable state |
| `EventUrls` | Current and previous HTTP request URLs |
| `EventUser` | Authenticated user at the time the event fired |

## SystemNotification Module Events

The `SystemNotification` module (in `Modules\SystemNotification\Events\`) defines a second set of domain events that fire on model lifecycle changes. These extend `ModelEvent` and are wired to their own listeners and notification classes:

| Event | Fired When |
|-------|------------|
| `ModelCreated` | Any model is created |
| `ModelUpdated` | Any model is updated |
| `ModelDeleted` | A model is soft-deleted |
| `ModelRestored` | A soft-deleted model is restored |
| `ModelForceDeleted` | A model is permanently deleted |
| `StateableUpdated` | A model transitions state via `HasStateable` |
| `AssignmentCreated` | A new `Assignment` is created |
| `AssignmentUpdated` | An existing `Assignment` is updated |
| `PaymentCompleted` | A payment completes |
| `PaymentFailed` | A payment fails |
| `UnreadChatMessage` | Unread chat messages exist for a `Chatable` model |

All these extend `ModelEvent`, so they automatically carry `EventUser`, `EventUrls`, `EventChanges`, and `EventStateable` context.

→ [System Notifications — event/listener/notification map](/system-reference/backend/notifications/system-notifications)

---

## Architecture Overview

```
[Action: model saved / user registers]
        │
        ▼
  Event dispatched
  (ModelEvent subclass or user event)
        │
        ▼
  Laravel event system
        │
        ├─► Listener::handle($event)
        │       └─ resolves {EventName}Notification
        │          sends mail if modularous.mail.enabled = true
        │
        └─► Broadcasting (ModelEvent only)
                broadcast on private channel: models.{model_id}
                broadcast on public channel:  model
                event name: modularous.{event_name}
```

→ [Broadcasting guide](/guide/broadcasting/overview)
