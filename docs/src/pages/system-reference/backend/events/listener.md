---
sidebarPos: 2
sidebarTitle: Listener
---

# Listener

`Unusualify\Modularous\Listeners\Listener`

Abstract base class for all Modularous listeners. Extend this class when creating listeners for `ModelEvent` subclasses. It handles mail-enabled notification dispatch using a convention-based class resolution.

## Class Signature

```php
abstract class Listener
{
    protected bool  $mailEnabled       = false;
    protected array $notificationPaths = [];

    public function __construct()
    public function handle($event): void
}
```

## Constructor

On instantiation the listener:

1. Reads `config('modularous.mail.enabled')` and sets `$mailEnabled`.
2. Adds the `SystemNotification` module's `Notifications/` directory to `$notificationPaths`.

## Notification Resolution Convention

When `handle()` is called, the listener derives the notification class name from the event class:

```
Event class name       →  Notification class name
─────────────────────────────────────────────────
OrderShippedEvent      →  OrderShippedEventNotification
ModelCreated           →  ModelCreatedNotification
```

The lookup scans every directory in `$notificationPaths` for a PHP file whose short class name matches `{EventName}Notification`.

```php
protected function getNotificationClass($event): ?string
```

Returns the fully-qualified class name if found, or `null` if no matching notification exists.

## `handle()` Method

```php
public function handle($event): void
```

If `$mailEnabled` is `true` and a matching notification class is found, it sends the notification immediately via `Notification::route('mail', ...)`:

```php
Notification::route('mail', $recipientAddress)
    ->notifyNow(new $notificationClass($event->model, $event->serializedData));
```

If mail is disabled or no notification class is found, `handle()` returns without doing anything.

## Extending Listener

Create a concrete listener for your module's events:

```php
namespace App\Modules\Orders\Listeners;

use Unusualify\Modularous\Listeners\Listener;
use App\Modules\Orders\Events\OrderShippedEvent;

class OrderShippedListener extends Listener
{
    public function handle(OrderShippedEvent $event): void
    {
        // Custom logic before calling parent mail dispatch:
        if ($event->wasChanged('status')) {
            // react to status change
        }

        // Delegate to Listener mail dispatch
        parent::handle($event);
    }
}
```

## Adding Notification Paths

If your module stores notification classes outside the default `SystemNotification` path, register additional paths before `handle()` is called:

```php
public function __construct()
{
    parent::__construct();
    $this->addNotificationPath(app_path('Modules/Orders/Notifications'));
}
```

Or merge multiple paths at once:

```php
$this->mergeNotificationPaths([
    app_path('Modules/Orders/Notifications'),
    app_path('Modules/Billing/Notifications'),
]);
```

## Configuration

| Config key | Type | Effect |
|------------|------|--------|
| `modularous.mail.enabled` | `bool` | When `true`, the listener will attempt to send a notification email. When `false`, `handle()` is a no-op. |

## Methods Reference

| Method | Visibility | Description |
|--------|-----------|-------------|
| `addNotificationPath(string $path)` | public | Append a single directory to `$notificationPaths` |
| `mergeNotificationPaths(array $paths)` | public | Merge an array of directories into `$notificationPaths` |
| `getNotificationClass($event)` | protected | Resolve and return the FQN of the matching notification, or `null` |
| `handle($event)` | public | Dispatch the notification mail if enabled |
