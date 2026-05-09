---
sidebarPos: 2
sidebarTitle: EventChanges
---

# EventChanges

`Unusualify\Modularous\Events\Traits\EventChanges`

Captures which model attributes and relationships changed before the event fired. Added to every `ModelEvent` subclass automatically.

## Source

```php
trait EventChanges
{
    protected array $changedAttributes    = [];
    protected array $changedRelationships = [];

    public function setupEventChanges(): void
    {
        if ($this->model instanceof Model) {
            $this->changedAttributes    = $this->model->getChanges();
            $this->changedRelationships = method_exists($this->model, 'getChangedRelationships')
                ? $this->model->getChangedRelationships()
                : [];
        }
    }

    public function wasChanged($values = null): bool
}
```

## Properties

| Property | Type | Description |
|----------|------|-------------|
| `$changedAttributes` | `array` | Keyed array of dirty model attributes — same shape as `$model->getChanges()` |
| `$changedRelationships` | `array` | Keyed array of changed relationships — populated when the model implements `getChangedRelationships()` |

Both properties are `protected`. Use `wasChanged()` to query them from outside the event.

## Methods

### `wasChanged($values = null): bool`

| Signature | Returns | Description |
|-----------|---------|-------------|
| `wasChanged()` | `bool` | `true` if any attribute **or** relationship changed |
| `wasChanged('key')` | `bool` | `true` if `'key'` is present in either `$changedAttributes` or `$changedRelationships` |
| `wasChanged(['a', 'b'])` | `bool` | `true` if any of the listed keys changed |

The method wraps the value argument with `Arr::wrap()`, so both a string and an array are accepted.

## Behaviour Notes

- `$changedAttributes` is populated from `$model->getChanges()`, which returns the values that were **just saved** — not the original values. It is only non-empty if the model was dirty at save time.
- `$changedRelationships` requires the model to implement a `getChangedRelationships()` method (e.g. from the `HasRepeaters` or `HasFiles` traits). If the method does not exist, the array is empty.
- If `$model` is not an Eloquent `Model` instance (e.g. a plain DTO), both arrays remain empty.

## Example

```php
public function handle(OrderUpdatedEvent $event): void
{
    // Check if anything at all changed
    if (! $event->wasChanged()) {
        return;
    }

    // React to a specific attribute
    if ($event->wasChanged('status')) {
        NotifyCustomer::dispatch($event->model);
    }

    // React to any of several fields
    if ($event->wasChanged(['amount', 'currency'])) {
        RecalculateTax::dispatch($event->model);
    }
}
```
