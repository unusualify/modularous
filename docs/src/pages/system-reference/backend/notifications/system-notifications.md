---
sidebarPos: 3
sidebarTitle: System Notifications
---

# System Notifications

`Modules\SystemNotification\Notifications\`

Fourteen concrete notification classes bundled in the `SystemNotification` module. All extend [`FeatureNotification`](./feature-notification) except the standalone `ModelDeletedNotification`, `ModelRestoredNotification`, `ModelForceDeletedNotification`, and `LogNotification`, which extend Laravel's `Notification` directly.

Every class implements `ShouldQueue` and uses the `Queueable` trait.

---

## Event → Listener → Notification Map

The `NotificationServiceProvider` wires up each event to its listener:

| Event | Listener | Notification dispatched |
|-------|----------|------------------------|
| `ModelCreated` | `ModelListener` | `ModelCreatedNotification` |
| `ModelUpdated` | `ModelListener` | `ModelUpdatedNotification` |
| `ModelRestored` | `ModelListener` | `ModelRestoredNotification` |
| `ModelDeleted` | `ModelForceDeletedListener` | `ModelDeletedNotification` |
| `ModelForceDeleted` | `ModelForceDeletedListener` | `ModelForceDeletedNotification` |
| `StateableUpdated` | `StateableListener` | `StateableUpdatedNotification` |
| `AssignmentCreated` | `AssignableListener` | `TaskCreatedNotification`, `TaskAssignedToAuthorizableNotification` |
| `AssignmentUpdated` | `AssignableListener` | `TaskUpdatedNotification` |
| `PaymentCompleted` | `PaymentListener` | `PaymentCompletedNotification` |
| `PaymentFailed` | `PaymentListener` | `PaymentFailedNotification` |

All listeners implement `ShouldHandleEventsAfterCommit`, so notifications are dispatched only after the database transaction commits.

---

## Model Lifecycle

### ModelCreatedNotification

Fired when any model is created.

```php
new ModelCreatedNotification($model)
```

**Default channels:** `mail`  
**Mail subject:** `"New {Model Headline}"`  
**Mail body:** `"A new {headline} was created called '{title}'."`

### ModelUpdatedNotification

Fired when any model is updated.

```php
new ModelUpdatedNotification($model)
```

**Default channels:** `mail`  
**Mail subject:** `"{Model Headline} Updated"`  
**Mail body:** `"The {headline} '{title}' has been updated."`

### ModelDeletedNotification

Fired when a model is soft-deleted.

```php
new ModelDeletedNotification($model, array $modelData)
```

**Default channels:** `mail`  

`$modelData` is a pre-serialized snapshot of the model passed by the listener (the model may no longer be fully accessible after deletion).

### ModelRestoredNotification

Fired when a soft-deleted model is restored.

```php
new ModelRestoredNotification($model)
```

**Default channels:** `mail`  
**Mail subject:** `"{Model Headline} Restored"`

### ModelForceDeletedNotification

Fired when a model is permanently deleted.

```php
new ModelForceDeletedNotification($model, array $modelData)
```

**Default channels:** `mail`  

Like `ModelDeletedNotification`, accepts a pre-serialized `$modelData` snapshot.

---

## Stateable {#stateable}

### StateableUpdatedNotification

Fired when a model transitions between states via the `HasStateable` trait.

```php
new StateableUpdatedNotification($model, $newState, $oldState)
```

Extends `FeatureNotification`.

| Argument | Description |
|----------|-------------|
| `$model` | The model whose state changed |
| `$newState` | The state the model transitioned **to** |
| `$oldState` | The state the model transitioned **from** |

**Default channels:** configurable via `config('modularous.notifications.StateableUpdatedNotification.channels')`  
**Mail subject:** `"{Model Headline} Status Changed"`  
**Mail body:** `"The status of the {headline} '{title}' has been changed to {state}."`  
**HTML body:** appends `$model->state_formatted` to the plain-text message

The listener notifies `$model->creator` if the creator relationship exists:

```php
if ($model->creator) {
    $model->creator->notify(new StateableUpdatedNotification($model, $newState, $oldState));
}
```

---

## Tasks {#tasks}

### TaskCreatedNotification

Fired when a new `Assignment` is created.

```php
new TaskCreatedNotification(Assignment $model)
```

Extends `FeatureNotification`.  
**Default channels:** configurable

### TaskUpdatedNotification

Fired when an existing `Assignment` is updated.

```php
new TaskUpdatedNotification(Assignment $model)
```

Extends `FeatureNotification`.  
**Default channels:** configurable

### TaskAssignedToAuthorizableNotification

Fired when a task is assigned to an authorizable entity (a user with role-based access).

```php
new TaskAssignedToAuthorizableNotification($model)
```

Extends `FeatureNotification`.  
**Default channels:** `database,mail` (read from `config('modularous.notifications.authorizable.channels', 'database,mail')`)

---

## Payment {#payment}

### PaymentCompletedNotification

Fired when a payment completes successfully.

```php
new PaymentCompletedNotification(Payment $model)
```

Extends `FeatureNotification`.  
**Default channels:** configurable

### PaymentFailedNotification

Fired when a payment fails.

```php
new PaymentFailedNotification(Payment $model)
```

Extends `FeatureNotification`.  
**Hard-coded default channels:** `['database', 'mail']`

---

## Chat {#chat}

### ChatableUnreadNotification

Fired when there are unread chat messages for a `Chatable` model.

```php
new ChatableUnreadNotification(Chat $model)
```

The constructor resolves the **chatable** (the parent model) from the `Chat` instance:

```php
parent::__construct($model->chatable);
```

Extends `FeatureNotification` and also implements `AfterSendable`.  
**Default channels:** configurable

---

## Log {#log}

### LogNotification

Dispatched by the Modularous Monolog handler when a critical log event occurs.

```php
new LogNotification(LogRecord $record)
```

Does **not** extend `FeatureNotification` — extends Laravel's `Notification` directly.  
**Default channels:** `mail`

The constructor extracts only serializable data from the `LogRecord`:

```php
$this->logData = [
    'level'    => $record->level->name,
    'message'  => $record->message,
    'context'  => $this->sanitizeContext($record->context),
    'datetime' => $record->datetime->format('Y-m-d H:i:s'),
    'channel'  => $record->channel,
];
```

---

## Customising System Notifications

All `FeatureNotification` subclasses support the full [callback system](./feature-notification#callback-system). Register callbacks early in the application lifecycle (e.g. `AppServiceProvider::boot()`):

```php
use Modules\SystemNotification\Notifications\ModelCreatedNotification;
use Modules\SystemNotification\Notifications\FeatureNotification;

// Override the subject for one notification type
ModelCreatedNotification::createMailSubject(
    fn($notifiable, $model, $default) => "New record: {$model->name}"
);

// Override the title field resolution globally
FeatureNotification::createModelTitleField(
    fn($model) => $model->display_name ?? $model->name ?? $model->id
);
```

### Per-class channel configuration

```php
// config/modularous.php
'notifications' => [
    \Modules\SystemNotification\Notifications\ModelCreatedNotification::class => [
        'channels' => 'mail,database',
    ],
    \Modules\SystemNotification\Notifications\StateableUpdatedNotification::class => [
        'channels' => 'database',
    ],
],
```
