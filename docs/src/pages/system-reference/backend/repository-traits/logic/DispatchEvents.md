---
sidebarPos: 6
sidebarTitle: DispatchEvents
---

# DispatchEvents

**Namespace**: `Unusualify\Modularous\Repositories\Logic\DispatchEvents`

Dispatches domain events after CUD operations, deferred until after the current database transaction commits. Events are fired by `SystemNotification` module event classes.

## Events Map

| Action | Event Class |
|--------|-------------|
| `create` / `store` | `ModelCreated` |
| `edit` / `update` | `ModelUpdated` |
| `delete` / `destroy` | `ModelDeleted` |
| `forceDelete` | `ModelForceDeleted` |
| `restore` | `ModelRestored` |

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `dispatchEvent` | `(Model $model, string $action): bool` | Looks up the action in `$events`, serialises the model for delete/force-delete events, then schedules `commitEvent()`. Returns `false` if the action has no registered event. |
| `commitEvent` | `(string $event, Model $model, ?array $serializedData): void` | Wraps `$event::dispatch(...)` in `DB::afterCommit()` — the event only fires if the transaction succeeds. |

## Why After Commit?

Dispatching after commit prevents event listeners from acting on records that may be rolled back. Listeners that update caches or send notifications will only run when the data is guaranteed to exist.

## Delete Serialisation

For `delete`, `destroy`, and `forceDelete` actions, `$model->toArray()` is captured **before** the commit callback so listeners receive the full record data even after it has been soft/hard deleted.

## Usage

```php
// Called automatically by Repository::create(), update(), delete(), etc.
// To add a custom event, override $events in your repository:

class PostRepository extends Repository
{
    protected $events = [
        ...parent::$events,
        'publish' => PostPublished::class,
    ];
}

// Trigger from a custom action:
$repo->dispatchEvent($post, 'publish');
```
