---
sidebarPos: 1
sidebarTitle: Overview
sidebarGroupTitle: Broadcasting
outline: deep
---

# Broadcasting

Modularous uses **Laravel Reverb** (or any Pusher-compatible driver) to broadcast `ModelEvent` subclasses over WebSockets. Every event that extends `ModelEvent` and implements `ShouldBroadcast` is automatically broadcast to two channels when it fires.

## In This Section

| Page | Purpose |
|------|---------|
| **Overview** (this page) | Setup, default channels, Echo integration |
| [Testing](./testing) | `Event::fake()` patterns, asserting channels and payloads |
| [Troubleshooting](./troubleshooting) | Common issues (`.` prefix, auth failures, duplicate dispatches, proxies) |

Class references live under `system-reference/backend/`:

- [ModelEvent](/system-reference/backend/events/model-event) — base class + `EventUser`, `EventUrls`, `EventChanges`, `EventStateable` traits
- [BroadcastManager](/system-reference/backend/services/broadcast-manager) — build frontend channel/event config from PHP

---

## How It Works

When a `ModelEvent` subclass that uses `InteractsWithBroadcasting` is dispatched, the constructor calls `$this->broadcastVia($this->broadcastService)` (defaults to `'reverb'`). Laravel then serializes and delivers the event payload over WebSockets.

### Default Channels

Every `ModelEvent` broadcasts on two channels:

| Channel | Type | Pattern | Purpose |
|---------|------|---------|---------|
| `models.{model_id}` | Private | One channel per model instance | Per-record real-time updates |
| `model` | Public | Single shared channel | All model updates across the app |

### Event Name Convention

The broadcast event name is derived from the class name:

```
ClassName            →  broadcast event name
────────────────────────────────────────────
OrderShippedEvent    →  modularity.order.shipped
ModelCreated         →  modularity.model.created
StateableUpdated     →  modularity.stateable.updated
```

The rule: strip `_event` suffix, convert to snake_case, replace `_` with `.`, prepend `modularity.`.

---

## Server Configuration

### 1. Install and Configure Reverb

```bash
php artisan reverb:install
```

In `config/broadcasting.php`, ensure the `reverb` connection is configured:

```php
'reverb' => [
    'driver'  => 'reverb',
    'key'     => env('REVERB_APP_KEY'),
    'secret'  => env('REVERB_APP_SECRET'),
    'app_id'  => env('REVERB_APP_ID'),
    'options' => [
        'host'   => env('REVERB_HOST'),
        'port'   => env('REVERB_PORT', 443),
        'scheme' => env('REVERB_SCHEME', 'https'),
        'useTLS' => env('REVERB_SCHEME', 'https') === 'https',
    ],
    'timeout' => null,
],
```

Set the default driver to `reverb` (or switch per-event):

```php
// config/broadcasting.php
'default' => env('BROADCAST_DRIVER', 'reverb'),
```

### 2. Start the Reverb Server

```bash
php artisan reverb:start
```

For production, use a process manager (Supervisor, systemd) to keep the server running.

---

## Creating a Broadcast Event

To make a `ModelEvent` subclass broadcast, add `ShouldBroadcast` and `InteractsWithBroadcasting`:

```php
use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Unusualify\Modularity\Events\ModelEvent;

class OrderShipped extends ModelEvent implements ShouldBroadcast
{
    use InteractsWithBroadcasting;
}
```

That's all — `ModelEvent` provides `broadcastOn()` and `broadcastAs()` automatically.

### Overriding Channels

To broadcast on custom channels instead of the defaults:

```php
use Illuminate\Broadcasting\PrivateChannel;

class OrderShipped extends ModelEvent implements ShouldBroadcast
{
    use InteractsWithBroadcasting;

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('orders.' . $this->model->id),
            new PrivateChannel('users.' . $this->model->user_id),
        ];
    }
}
```

### Overriding the Broadcast Driver

To use a different driver for a specific event:

```php
class OrderShipped extends ModelEvent implements ShouldBroadcast
{
    use InteractsWithBroadcasting;

    public string $broadcastService = 'pusher'; // override from 'reverb'
}
```

---

## BroadcastManager — Building Frontend Config

`BroadcastManager::forModel()` inspects a list of event classes for a given model and returns a structured config array that can be passed to the frontend so Laravel Echo can subscribe dynamically.

```php
use Unusualify\Modularity\Services\BroadcastManager;
use Modules\SystemNotification\Events\ModelCreated;
use Modules\SystemNotification\Events\ModelUpdated;
use Modules\SystemNotification\Events\StateableUpdated;

$broadcastConfig = BroadcastManager::forModel($order, [
    ModelCreated::class,
    ModelUpdated::class,
    StateableUpdated::class,
]);
```

The returned array has the shape:

```php
[
    [
        'name'   => 'private-orders.42',
        'type'   => 'private',
        'events' => [
            ['event' => 'modularity.model.created'],
            ['event' => 'modularity.model.updated'],
            ['event' => 'modularity.stateable.updated'],
        ],
    ],
    [
        'name'   => 'model',
        'type'   => 'public',
        'events' => [/* same events */],
    ],
]
```

Pass this to the frontend via Inertia shared data or a Blade variable:

```php
// In your controller or middleware:
Inertia::share('broadcastConfig', BroadcastManager::forModel($model, $eventClasses));
```

→ [BroadcastManager service reference](/system-reference/backend/services/broadcast-manager)

---

## Frontend — Subscribing with Laravel Echo

### 1. Install Echo and the Reverb client

```bash
npm install laravel-echo pusher-js
```

### 2. Configure Echo

```js
// resources/js/bootstrap.js
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher

window.Echo = new Echo({
    broadcaster:      'reverb',
    key:              import.meta.env.VITE_REVERB_APP_KEY,
    wsHost:           import.meta.env.VITE_REVERB_HOST,
    wsPort:           import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort:          import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS:         (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
})
```

### 3. Subscribe Using BroadcastManager Config

```js
function subscribeToBroadcast(broadcastConfig) {
    broadcastConfig.forEach(({ name, type, events }) => {
        const channel = type === 'private'
            ? Echo.private(name)
            : Echo.channel(name)

        events.forEach(({ event }) => {
            channel.listen(`.${event}`, (payload) => {
                console.log(`Event ${event} received:`, payload)
                // update your Vue store / reactive state here
            })
        })
    })
}

// In a Vue component (using Inertia shared data):
const { broadcastConfig } = usePage().props
subscribeToBroadcast(broadcastConfig)
```

::: tip Event name prefix
Laravel Echo requires a leading `.` before custom event names: `.modularity.model.created`. Without it, Echo treats the name as a Laravel event class string.
:::

### 4. Authorizing Private Channels

Private channels require the `Broadcast::channel()` authorization to be registered. Add to `routes/channels.php`:

```php
use Illuminate\Support\Facades\Broadcast;

// Authorize any authenticated user to subscribe to their model's channel
Broadcast::channel('models.{modelId}', function ($user, $modelId) {
    return $user !== null; // adjust to your auth logic
});
```

---

## Environment Variables

```dotenv
BROADCAST_DRIVER=reverb

REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

# Frontend (Vite)
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```
