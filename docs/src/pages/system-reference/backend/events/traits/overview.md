---
sidebarPos: 1
sidebarTitle: Overview
sidebarGroupTitle: Event Traits
---

# Event Traits

Four traits are mixed into every `ModelEvent` subclass automatically. They populate contextual data — who triggered the event, what changed, where the request came from, and what state the model transitioned through — so listeners never have to repeat that boilerplate.

## Trait Summary

| Trait | File | What it captures |
|-------|------|-----------------|
| [EventUser](./event-user) | `Events/Traits/EventUser.php` | Authenticated user at fire time |
| [EventUrls](./event-urls) | `Events/Traits/EventUrls.php` | Current and previous HTTP URLs |
| [EventChanges](./event-changes) | `Events/Traits/EventChanges.php` | Dirty attributes and changed relationships |
| [EventStateable](./event-stateable) | `Events/Traits/EventStateable.php` | State machine transition details |

## Setup Lifecycle

`ModelEvent::__construct()` calls each trait's setup method in this order:

```
new SomeModelEvent($model)
    │
    ├─ setupEventUser()       → $this->user
    ├─ setupEventUrls()       → $this->recentUrl, $this->previousUrl
    ├─ setupEventChanges()    → $this->changedAttributes, $this->changedRelationships
    └─ setupEventStateable()  → $this->hasStateable, $this->stateableChanged, ...
```

All properties are set by the time any listener receives the event.

## Using Traits in a Listener

```php
public function handle(SomeModelEvent $event): void
{
    // EventUser
    if ($event->hasUser()) {
        $userId = $event->getUser()->id;
    }

    // EventUrls
    $from = $event->getPreviousUrl();
    $to   = $event->getRecentUrl();

    // EventChanges
    if ($event->wasChanged('status')) {
        // status attribute or relationship changed
    }

    // EventStateable
    if ($event->stateableChanged) {
        $transition = $event->previousStateableState . ' → ' . $event->currentStateableState;
    }
}
```
