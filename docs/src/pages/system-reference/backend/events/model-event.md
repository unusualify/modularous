---
sidebarPos: 3
sidebarTitle: ModelEvent
outline: deep
---

# ModelEvent

`Unusualify\Modularous\Events\ModelEvent`

**File**: `src/Events/ModelEvent.php`

Abstract base class for all model-level events. Extend this class to create events that fire when a model is created, updated, or deleted. It wires up broadcasting support via Laravel Reverb and automatically populates contextual data through four traits.

## Class Signature

```php
abstract class ModelEvent
{
    use EventUrls, EventChanges, EventStateable, EventUser;

    public string $modelType;
    public string $broadcastService = 'reverb';

    public function __construct(public $model, public $serializedData = null)
}
```

## Properties

| Property | Type | Description |
|----------|------|-------------|
| `$model` | mixed | The Eloquent model instance that triggered the event |
| `$serializedData` | mixed\|null | Optional pre-serialized payload (used by notification classes) |
| `$modelType` | string | Fully-qualified class name of `$model` |
| `$broadcastService` | string | Broadcast driver; defaults to `'reverb'` |

## Constructor

```php
new OrderShipped($order);                    // model only
new OrderShipped($order, $serializedData);   // with pre-serialized payload
```

The constructor:

1. Captures `modelType = get_class($model)`.
2. Runs the four `setup*()` methods from the composed traits (user, URLs, changes, stateable).
3. If the event uses `InteractsWithBroadcasting`, calls `$this->broadcastVia($this->broadcastService)`.

## Defaults You Get For Free

| Method | Default return | Override when |
|--------|---------------|---------------|
| `broadcastOn()` | `[PrivateChannel('models.{id}'), Channel('model')]` | You need custom/per-user channels |
| `broadcastAs()` | `'modularous.' . snake-dot class name` | You want a stable wire name |
| `broadcastWhen()` | `true` | You want conditional broadcasting |
| `$broadcastService` | `'reverb'` | You want Pusher/Ably per event |

## Broadcasting

`ModelEvent` implements Laravel's `ShouldBroadcast` contract via `InteractsWithBroadcasting`. When the using class includes `InteractsWithBroadcasting`, the driver is set to `$broadcastService` during construction.

### Channels

| Channel | Type | Pattern |
|---------|------|---------|
| `models.{model_id}` | Private | Per-model updates |
| `model` | Public | All model updates |

### Event Name

The broadcast event name follows the convention:

```
modularous.{snake_case_event_without_event_suffix}
```

For example, a class named `UserCreatedEvent` broadcasts as `modularous.user.created`.

This is resolved by:

```php
public function broadcastAs(): string
{
    return 'modularous.' . Str::replace('_', '.', 
        Str::replace('_event', '', Str::snake(get_class_short_name($this)))
    );
}
```

### Overriding `broadcastWhen`

```php
class OrderShipped extends ModelEvent implements ShouldBroadcast
{
    use InteractsWithBroadcasting;

    public function broadcastWhen(): bool
    {
        return $this->wasChanged('status') && $this->model->status === 'shipped';
    }
}
```

## Event Traits

Each trait is set up inside the constructor before broadcasting is configured. See the individual trait pages for full property/method references and usage examples.

| Trait | What it captures | Page |
|-------|-----------------|------|
| `EventUser` | Authenticated user at fire time | [EventUser →](./traits/event-user) |
| `EventUrls` | Current and previous HTTP URLs | [EventUrls →](./traits/event-urls) |
| `EventChanges` | Dirty attributes and changed relationships | [EventChanges →](./traits/event-changes) |
| `EventStateable` | State machine transition details | [EventStateable →](./traits/event-stateable) |

## Extending ModelEvent

```php
use Unusualify\Modularous\Events\ModelEvent;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\InteractsWithBroadcasting;

class OrderShipped extends ModelEvent implements ShouldBroadcast
{
    use InteractsWithBroadcasting;
}
```

Dispatching the event:

```php
event(new OrderShipped($order));

// With serialized payload for notification classes:
event(new OrderShipped($order, $serializedData));
```

Listening in your module's `EventServiceProvider`:

```php
protected $listen = [
    OrderShipped::class => [
        OrderShippedListener::class,
    ],
];
```

## Checking Changes in a Listener

```php
public function handle(OrderShipped $event): void
{
    // Attribute / relationship change (EventChanges)
    if ($event->wasChanged('status')) {
        // status changed on this save
    }

    // State machine transition (EventStateable)
    if ($event->hasStateable && $event->stateableChanged) {
        $from = $event->previousStateableState;
        $to   = $event->currentStateableState;
    }

    // Who triggered it (EventUser)
    if ($event->hasUser()) {
        $actor = $event->getUser();
    }
}
```

See [Event Traits](./traits/overview) for the full reference on each trait.

## Broadcast Payload Shape

Because all four trait properties are `public`, they are automatically serialized into the broadcast payload. Clients listening on `.modularous.*` events receive an object with:

```jsonc
{
  "model":        { /* full model, per broadcastWith() or default */ },
  "modelType":    "App\\Models\\Order",
  "user":         { /* auth user or null */ },
  "recentUrl":    "https://app.example.com/admin/orders/42",
  "previousUrl":  "https://app.example.com/admin/orders",
  "changedAttributes":    { "status": "shipped" },
  "changedRelationships": {},
  "hasStateable":           true,
  "stateableChanged":       true,
  "previousStateableState": "paid",
  "currentStateableState":  "shipped"
}
```

Use `broadcastWith()` on your subclass to trim or reshape the payload.

```php
public function broadcastWith(): array
{
    return [
        'id'       => $this->model->id,
        'status'   => $this->currentStateableState,
        'actor'    => $this->user?->name,
    ];
}
```

## Subclassing Checklist

When creating a new broadcast event:

- [ ] Extend `Unusualify\Modularous\Events\ModelEvent`
- [ ] Implement `Illuminate\Contracts\Broadcasting\ShouldBroadcast`
- [ ] Use `Illuminate\Broadcasting\InteractsWithBroadcasting`
- [ ] (Optional) Override `broadcastOn()` for custom channels
- [ ] (Optional) Override `broadcastWhen()` for conditional dispatch
- [ ] (Optional) Override `broadcastWith()` to shape the payload
- [ ] Register a channel authorization in `routes/channels.php` for private channels

## Related

- [Event Traits](./traits/overview) — full reference for `EventUser`, `EventUrls`, `EventChanges`, `EventStateable`
- [Broadcasting Overview](/guide/broadcasting/overview) — setup, channels, Echo integration
- [BroadcastManager](/system-reference/backend/services/broadcast-manager) — build frontend config from a list of event classes
- [Testing Broadcasts](/guide/broadcasting/testing) — `Event::fake()` patterns for broadcast events
- [Broadcasting Troubleshooting](/guide/broadcasting/troubleshooting) — common issues and fixes
