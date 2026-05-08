---
sidebarPos: 16
sidebarTitle: MessageStage
---

# MessageStage

**File**: `src/Services/MessageStage.php`

A PHP 8.1 **backed enum** providing a type-safe vocabulary for flash messages, alert payloads, and notification status values across the application.

## Definition

```php
enum MessageStage: string
{
    case SUCCESS = 'success';
    case ERROR   = 'error';
    case WARNING = 'warning';
    case INFO    = 'info';
}
```

## Cases

| Case | Value | Typical Use |
|------|-------|-------------|
| `SUCCESS` | `'success'` | Operation completed successfully |
| `ERROR` | `'error'` | Operation failed |
| `WARNING` | `'warning'` | Non-fatal issue or cautionary state |
| `INFO` | `'info'` | Informational message |

## Usage

```php
use Unusualify\Modularity\Services\MessageStage;

// In a controller — flash a typed message
session()->flash('message', [
    'stage'   => MessageStage::SUCCESS->value,
    'content' => __('Record saved successfully.'),
]);

// In Inertia shared data
Inertia::share('flash', [
    'stage'   => MessageStage::ERROR->value,
    'content' => $errorMessage,
]);

// In a match expression
$stage = MessageStage::from($request->input('stage'));
$cssClass = match ($stage) {
    MessageStage::SUCCESS => 'alert-success',
    MessageStage::ERROR   => 'alert-danger',
    MessageStage::WARNING => 'alert-warning',
    MessageStage::INFO    => 'alert-info',
};
```

## Frontend Integration

The `useAlert` Vue hook reads the `stage` value (the string `'success'`, `'error'`, etc.) from Inertia flash data and maps it to the corresponding Vuetify alert variant.
