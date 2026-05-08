---
sidebarPos: 3
sidebarTitle: EventStateable
---

# EventStateable

`Unusualify\Modularity\Events\Traits\EventStateable`

Captures state machine transition data when the model uses the `HasStateable` entity trait. Added to every `ModelEvent` subclass automatically. Properties are only meaningful when `$hasStateable` is `true`.

## Source

```php
trait EventStateable
{
    public bool        $hasStateable           = false;
    public bool        $stateableChanged        = false;
    public string|null $previousStateableState = null;
    public string|null $currentStateableState  = null;

    public function setupEventStateable(): void
    {
        if ($this->model instanceof Model && classHasTrait($this->model, HasStateable::class)) {
            $this->hasStateable           = true;
            $this->stateableChanged        = $this->model->stateableChanged();
            $this->previousStateableState = $this->model->previousStateableState();
            $this->currentStateableState  = $this->model->currentStateableState();
        }
    }
}
```

## Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$hasStateable` | `bool` | `false` | `true` when the model uses `HasStateable` |
| `$stateableChanged` | `bool` | `false` | `true` when the model transitioned to a new state during this save |
| `$previousStateableState` | `string\|null` | `null` | State name before the transition |
| `$currentStateableState` | `string\|null` | `null` | State name after the transition |

## Behaviour Notes

- Setup is skipped entirely when the model does not use `HasStateable`. All properties stay at their defaults.
- `$stateableChanged` can be `false` even on a model that has `HasStateable` — when a save occurs but the state field did not change.
- State names are string identifiers as defined in the model's stateable configuration (e.g. `'draft'`, `'published'`, `'archived'`).

## Example

```php
public function handle(ArticleUpdatedEvent $event): void
{
    // Guard: only act if this model uses the state machine
    if (! $event->hasStateable) {
        return;
    }

    // Guard: only act on actual transitions
    if (! $event->stateableChanged) {
        return;
    }

    $from = $event->previousStateableState; // e.g. 'draft'
    $to   = $event->currentStateableState;  // e.g. 'published'

    match ($to) {
        'published' => NotifySubscribers::dispatch($event->model),
        'archived'  => CleanupDraftMedia::dispatch($event->model),
        default     => null,
    };
}
```

## Related

- [`HasStateable`](/system-reference/backend/entity-traits/overview) entity trait — defines the state machine on the model side.
