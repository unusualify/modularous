---
sidebarPos: 4
sidebarTitle: EventUrls
---

# EventUrls

`Unusualify\Modularity\Events\Traits\EventUrls`

Captures the current and previous HTTP request URLs when the event is constructed. Added to every `ModelEvent` subclass automatically.

## Source

```php
trait EventUrls
{
    public string $recentUrl;
    public string $previousUrl;

    public function setupEventUrls(): void
    {
        $this->recentUrl  = url()->current()  ?? null;
        $this->previousUrl = url()->previous() ?? null;
    }

    public function getRecentUrl(): string
    public function getPreviousUrl(): string
}
```

## Properties

| Property | Type | Description |
|----------|------|-------------|
| `$recentUrl` | `string\|null` | The URL of the request that triggered the event (`url()->current()`) |
| `$previousUrl` | `string\|null` | The URL the user navigated from (`url()->previous()`) |

## Methods

| Method | Returns | Description |
|--------|---------|-------------|
| `getRecentUrl()` | `string\|null` | Returns `$recentUrl` |
| `getPreviousUrl()` | `string\|null` | Returns `$previousUrl` |

## Behaviour Notes

- Both values are `null` when the event fires outside an HTTP context (queued jobs, CLI commands).
- `url()->previous()` reads from the session; it may be `null` on the first request of a session.

## Example

```php
public function handle(SomeModelEvent $event): void
{
    $current  = $event->getRecentUrl();
    $previous = $event->getPreviousUrl();

    if ($current && $previous) {
        logger("Navigated from {$previous} to {$current}");
    }
}
```
