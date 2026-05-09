---
sidebarPos: 9
sidebarTitle: BroadcastManager
---

# BroadcastManager

**File**: `src/Services/BroadcastManager.php`

Extracts WebSocket broadcasting configuration from Modularous event classes tied to a specific model. The resulting config array is passed to the frontend (via Inertia shared data or the Blade footer) so that **Laravel Echo** can subscribe to the correct channels dynamically, without hardcoding channel names in Vue components.

## How It Works

Given a model and a list of event class names, `BroadcastManager` instantiates each event with the model, reads the channels from `broadcastOn()` and the event name from `broadcastAs()`, then groups them into a structured array per channel.

## Key Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `forModel` *(static)* | `forModel($model, array $eventClasses): array` | Primary entry point. Builds the broadcast config for the model. |
| `getBroadcastConfiguration` | `getBroadcastConfiguration(): array` | Returns the grouped channel + event array. Called internally by `forModel`. |

## Return Structure

```php
[
    [
        'name'   => 'private-orders.42',   // channel name
        'type'   => 'private',              // 'private' | 'public'
        'events' => [
            ['event' => 'OrderCreated'],
            ['event' => 'OrderUpdated'],
        ],
    ],
    // ... additional channels
]
```

## Example

```php
use Unusualify\Modularous\Services\BroadcastManager;
use Modules\SystemNotification\Events\ModelCreated;
use Modules\SystemNotification\Events\ModelUpdated;

$broadcastConfig = BroadcastManager::forModel($order, [
    ModelCreated::class,
    ModelUpdated::class,
]);

// Pass to Inertia or Blade:
Inertia::share('broadcastConfig', $broadcastConfig);
```

## Event Requirements

Each event class passed to `forModel` must:

1. Accept the model as its first constructor argument
2. Implement `broadcastOn()` returning an array of `Channel` or `PrivateChannel` instances
3. Optionally implement `broadcastAs()` returning the event name string (defaults to class basename)

```php
use Illuminate\Broadcasting\PrivateChannel;
use Unusualify\Modularous\Events\ModelEvent;

class OrderCreated extends ModelEvent
{
    public function broadcastOn(): array
    {
        return [new PrivateChannel('orders.' . $this->model->id)];
    }

    public function broadcastAs(): string
    {
        return 'OrderCreated';
    }
}
```

## Frontend Integration

Once the config is shared with Vue via Inertia, use the `useBroadcast` composable or subscribe directly with Laravel Echo:

```js
broadcastConfig.forEach(({ name, type, events }) => {
    const channel = type === 'private'
        ? Echo.private(name)
        : Echo.channel(name)

    events.forEach(({ event }) => {
        channel.listen(event, (payload) => handleEvent(event, payload))
    })
})
```
