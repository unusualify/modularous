---
sidebarPos: 5
sidebarTitle: HasStateable
---

# HasStateable

**Namespace**: `Unusualify\Modularity\Entities\Traits\HasStateable`

Implements a configurable state machine backed by a `Stateable` morph record and a shared `states` table. Dispatches `StateableUpdated` on every transition and provides appended attributes for the current state.

---

## Boot Behavior

| Event | Action |
|-------|--------|
| `saving` | Detects pending stateable update from `stateable_id`; clears fillable helpers |
| `created` | Creates `State` DB records for all `$default_states` and sets the initial state |
| `retrieved` | Sets `stateable_id` from the current `state` relationship |
| `saved` | Calls `updateStateable()` to write the new state and dispatch `StateableUpdated` |

---

## Relationships

```php
public function state(): HasOneThrough   // → State through Stateable
public function stateable(): MorphOne    // → Stateable (pivot record)
```

---

## Appended Attributes

Appended via `initializeHasStateable()`:

| Attribute | Type | Description |
|-----------|------|-------------|
| `state_formatted` | `string` | HTML chip `<span>` with the state's color, icon, and translated name |
| `states` | `Collection` | All available `State` records for this model type |

---

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `getStates` | `(): Collection` (static) | Returns (and caches for 1 hour) all `State` records for the model's `$default_states` codes |
| `hydrateState` | `(State $state): State` | Fills icon, color, and translations onto a `State` from the model's configuration |
| `getStateAttribute` | `(): ?State` | Returns the current state with configuration applied |
| `getDefaultStates` | `(): array` (static) | Returns the formatted array of all configured states |
| `getInitialState` | `(): ?array` (static) | Returns the initial state definition |
| `getStateConfiguration` | `(string $code): array` (static) | Returns `icon` + `color` for a state code |
| `stateableChanged` | `(): bool` | Returns `true` if the state was changed in the last save |
| `previousStateableState` | `(): ?State` | Returns the state before the last transition |
| `currentStateableState` | `(): ?State` | Returns the state after the last transition |
| `syncStateData` | `(): array` (static) | Creates any `State` DB records that are missing for the model |

---

## Fillable Helpers

The following virtual fields are merged into `$fillable` and removed before save:

| Field | Description |
|-------|-------------|
| `initial_stateable` | Set to a state code to override the initial state on creation |
| `stateable_id` | Set to a `State.id` to transition to that state |

---

## Configuration

```php
// In your model
protected static $default_states = [
    'draft',
    ['code' => 'published', 'icon' => 'mdi-check', 'color' => 'success', 'en' => ['name' => 'Published']],
    'archived',
];

protected static $initial_state = 'draft';
// or as an array:
protected static $initial_state = ['code' => 'draft'];

// Override the state model (default: Modules\SystemUtility\Entities\State)
protected static $stateModel = State::class;
```

---

## Events

`Modules\SystemNotification\Events\StateableUpdated::dispatch($model, $newState, $oldState)` — fired on every state transition.

---

## Usage

```php
use Unusualify\Modularity\Entities\Traits\HasStateable;

class Article extends Model
{
    use HasStateable;

    protected static $default_states = ['draft', 'published', 'archived'];
    protected static $initial_state  = 'draft';
}

// Transition state
$article->stateable_id = State::where('code', 'published')->value('id');
$article->save();

// Read state
$article->state->code;           // 'published'
$article->state_formatted;       // HTML chip

// Check transition
$article->stateableChanged();    // true after a transition
$article->previousStateableState()->code; // 'draft'

// Sync missing state DB records
Article::syncStateData();
```
