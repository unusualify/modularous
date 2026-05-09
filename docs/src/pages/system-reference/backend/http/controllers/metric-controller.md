---
sidebarPos: 16
sidebarTitle: MetricController
---

# MetricController

**File**: `src/Http/Controllers/MetricController.php`  
**Namespace**: `Unusualify\Modularous\Http\Controllers`  
**Extends**: `Illuminate\Routing\Controller`

Single-action invokable controller that resolves metric values for dashboard widgets. Supports connector-based metric providers and date-range filtering.

## Signature

```php
public function __invoke(Request $request): JsonResponse
```

## Request Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `items` | `array` | List of metric item definitions to resolve |
| `start_date` | `date` | Start of the reporting period (optional) |
| `end_date` | `date` | End of the reporting period (optional) |

## Behaviour

For each item in `items`:

1. Resolves the connector class registered for the item type.
2. Injects date-range parameters into the connector if provided.
3. Updates any connector-specific parameters from the request.
4. Calls the connector to retrieve the metric value (which may be a callable).
5. Pushes the resolved value to a response collection.

Returns all resolved metric values as a JSON array.

## Connectors

Connectors are classes registered in the module config that know how to retrieve a specific metric (e.g. total orders, active users). Each connector implements a common interface that accepts optional date and parameter overrides.

Callable metric values allow connectors to return lazy-evaluated data — the value is only computed when the controller invokes it.
