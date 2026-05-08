---
sidebarPos: 5
sidebarTitle: State Machine Workflow
outline: deep
---

# Recipe — State Machine Workflow

**Goal**: Model a record that moves through named states (e.g. `draft → review → approved → published`) with transition history, authorization, and a dedicated UI input.

**Time**: ~15 minutes.

## When to Use Which Trait

Modularous has two related traits — pick based on what you need:

| Trait | Use for |
|-------|---------|
| `HasStateable` | Simple named states on a record (draft / active / archived) — one row in `states` table |
| `Processable` | Full process lifecycle with history and status transitions per step |

This recipe covers **both** — start with `HasStateable` and escalate to `Processable` when you need process history.

## 1. Add `HasStateable` to your model

```php
use Unusualify\Modularity\Entities\Traits\HasStateable;

class Article extends Model
{
    use HasStateable;
}
```

This installs:

- `morphOne(State::class, 'stateable')` relation
- `stateable()`, `stateable_status` accessors
- `stateableChanged()`, `currentStateableState()`, `previousStateableState()` helpers

## 2. Add the repository trait

```php
use Unusualify\Modularity\Repositories\Traits\StateableTrait;

class ArticleRepository extends Repository
{
    use StateableTrait;
}
```

## 3. Configure the state list

Define the states in your repository (or a dedicated config):

```php
// ArticleRepository.php
public function getStateableList(): array
{
    return [
        ['value' => 'draft',     'label' => 'Draft'],
        ['value' => 'review',    'label' => 'In Review'],
        ['value' => 'approved',  'label' => 'Approved'],
        ['value' => 'published', 'label' => 'Published'],
    ];
}
```

## 4. Add the input to your hydrate

```php
public function getInputs(): array
{
    return [
        ['type' => 'text',      'name' => 'title'],
        ['type' => 'textarea',  'name' => 'body'],
        ['type' => 'stateable', 'name' => 'stateable_id'],
    ];
}
```

The `stateable` hydrate auto-pulls `items` from `getStateableList()` and emits a `select` input.

## 5. Query by state

```php
Article::whereHas('stateable', fn($q) => $q->where('status', 'published'))->get();

// Current state
$article->currentStateableState(); // 'published'
$article->stateable_status;        // accessor
```

## 6. Listen for state transitions

Create a broadcast event that fires on state changes:

```bash
php artisan modularity:make:event StateableUpdated Blog
```

Then in the event (already extends `ModelEvent`):

```php
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Unusualify\Modularity\Events\ModelEvent;

class StateableUpdated extends ModelEvent implements ShouldBroadcast
{
    use InteractsWithBroadcasting;

    public function broadcastWhen(): bool
    {
        return $this->hasStateable && $this->stateableChanged;
    }
}
```

`$hasStateable`, `$stateableChanged`, `$previousStateableState`, `$currentStateableState` are auto-populated by the [`EventStateable`](/system-reference/backend/events/traits/event-stateable) trait in `ModelEvent`.

Dispatch it from your repository's `afterSave()`:

```php
public function afterSave($object, $fields)
{
    parent::afterSave($object, $fields);

    if ($object->stateableChanged()) {
        event(new StateableUpdated($object));
    }
}
```

## 7. Subscribe on the frontend

Use `BroadcastManager` to share channel config and subscribe with Echo:

```php
// Controller
$broadcastConfig = BroadcastManager::forModel($article, [
    StateableUpdated::class,
]);

return inertia('Article/Show', compact('article', 'broadcastConfig'));
```

```js
// Vue
import { onMounted, onBeforeUnmount } from 'vue'
import { usePage } from '@inertiajs/vue3'

const { broadcastConfig } = usePage().props

onMounted(() => {
    broadcastConfig.forEach(({ name, type, events }) => {
        const channel = type === 'private' ? Echo.private(name) : Echo.channel(name)
        events.forEach(({ event }) => {
            channel.listen(`.${event}`, ({ currentStateableState }) => {
                // update your Vue state here
            })
        })
    })
})
```

See [Broadcasting](/guide/broadcasting/overview) for the full flow.

## 8. Verify

1. Create an Article — state defaults to `draft`.
2. Edit and switch state to `review` — the broadcast event fires.
3. `$article->previousStateableState()` returns `draft`; `currentStateableState()` returns `review`.

## Escalating to `Processable`

Use `Processable` when you need:

- **Per-step history** (`process_histories` table)
- **Assignment per step** (combined with `Assignable`)
- **Conditional transitions** with explicit `advance()` / `rollback()` calls

```php
use Unusualify\Modularity\Entities\Traits\Processable;

class Claim extends Model
{
    use Processable;
}
```

Then expose the `process` input:

```php
[
    'type' => 'process',
    'name' => 'process_id',
]
```

See [Processable](/guide/module-features/processable) for the full pattern.

## Next Steps

- [HasStateable](/guide/module-features/stateable) — entity trait reference
- [Processable](/guide/module-features/processable) — richer state machine with history
- [Events & Broadcasting](/guide/broadcasting/overview) — real-time state updates
- [ModelEvent / EventStateable](/system-reference/backend/events/traits/event-stateable) — auto-captured state context
