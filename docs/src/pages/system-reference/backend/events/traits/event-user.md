---
sidebarPos: 5
sidebarTitle: EventUser
---

# EventUser

`Unusualify\Modularous\Events\Traits\EventUser`

Captures the currently authenticated user at the moment the event is constructed. Added to every `ModelEvent` subclass automatically.

## Source

```php
trait EventUser
{
    public $user;

    public function setupEventUser(): void
    {
        $this->user = Auth::user();
    }

    public function hasUser(): bool
    public function getUser(): Model
}
```

## Properties

| Property | Type | Description |
|----------|------|-------------|
| `$user` | `Illuminate\Database\Eloquent\Model\|null` | The authenticated user, or `null` when the event fires in an unauthenticated context (queues, CLI) |

## Methods

| Method | Returns | Description |
|--------|---------|-------------|
| `hasUser()` | `bool` | `true` when `$user` is not `null` |
| `getUser()` | `Model\|null` | Returns the user model |

## Behaviour Notes

- `$user` is resolved via `Auth::user()` — it reflects the guard active at fire time.
- In queued jobs or console contexts `Auth::user()` returns `null`, so always guard with `hasUser()` before reading user data.

## Example

```php
public function handle(SomeModelEvent $event): void
{
    if (! $event->hasUser()) {
        return; // fired from a queue or CLI — no auth context
    }

    $actor = $event->getUser();
    activity()->causedBy($actor)->log('model updated');
}
```
