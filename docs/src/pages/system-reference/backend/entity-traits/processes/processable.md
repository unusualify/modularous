---
sidebarPos: 2
sidebarTitle: Processable
---

# Processable

**Namespace**: `Unusualify\Modularous\Entities\Traits\Processable`

Single-process workflow: models go through a `preparing → waiting_for_confirmation → confirmed / rejected` lifecycle. Uses `HasFileponds` (for evidence file uploads) and `ProcessableScopes` for query filtering.

---

## Boot Behavior

| Event | Action |
|-------|--------|
| `created` | Creates an initial `Process` record with status `ProcessStatus::PREPARING` |
| `saved` | If `processable_status` is set, calls `setProcessStatus` and touches the model |

---

## Relationships

```php
public function process(): MorphOne                      // → active Process record
public function processHistories(): HasManyThrough       // → ProcessHistory through Process
public function processHistory(): HasOneThrough          // → Latest ProcessHistory through Process
```

---

## Computed Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `has_process_history` | `bool` | `true` if at least one `ProcessHistory` record exists |
| `process_history_status` | `string\|null` | Status of the latest `ProcessHistory` |
| `process_history_reason` | `string\|null` | Reason from the latest `ProcessHistory` |

---

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `setProcessStatus` | `(string $status, ?string $reason = null): void` | Upserts the `Process` record and creates a `ProcessHistory` entry |
| `sendForConfirmation` | `(): void` | Transitions to `WAITING_FOR_CONFIRMATION` |
| `confirm` | `(): void` | Transitions to `CONFIRMED` |
| `reject` | `(string $reason): void` | Transitions to `REJECTED` with a reason |
| `isProcessStatus` | `(ProcessStatus $status): bool` | Checks if current status matches |

---

## Scopes

Provided by `ProcessableScopes`:

| Scope | Description |
|-------|-------------|
| `scopePreparing()` | Models with `preparing` status |
| `scopeWaitingForConfirmation()` | Models pending review |
| `scopeConfirmed()` | Models with confirmed process |
| `scopeRejected()` | Models with rejected process |

---

## Usage

```php
use Unusualify\Modularous\Entities\Traits\Processable;

class Application extends Model
{
    use Processable;
}

// Workflow transitions
$application->sendForConfirmation();
$application->confirm();
$application->reject('Missing required documents');

// Check status
$application->isProcessStatus(ProcessStatus::CONFIRMED); // true
$application->process_history_status;

// Query
Application::waitingForConfirmation()->get();
Application::confirmed()->latest()->get();
```
