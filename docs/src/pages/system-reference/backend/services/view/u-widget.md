---
sidebarPos: 2
sidebarTitle: UWidget
---

# UWidget

**File**: `src/Services/View/UWidget.php`  
**Extends**: `UComponent`

`UWidget` extends `UComponent` with **Connector-aware attribute hydration** for dashboard widgets. When an attributes array contains a `connector` key, `UWidget` calls `init_connector()` to fetch live data and merges it into the component schema automatically.

## Difference from UComponent

`UWidget::setAttributes()` is overridden to:

1. Extract grid column attributes (`col` key → `$this->attributes`).
2. Dispatch to a component-specific method based on the `component` key (e.g. `component: 'ue-table'` → `setTableAttributes()`).
3. Fall back to the generic `setComponentAttributes()` when no specific handler exists.

## Built-in Attribute Handlers

| Method | Triggered when `component` is | Behaviour |
|--------|-------------------------------|-----------|
| `setTableAttributes($attrs)` | `ue-table` | Runs the connector, converts items to array, merges `items`, `route`, `repository`, `module` into the table's attributes |
| `setComponentAttributes($attrs)` | Any other component | Same as above — generic connector + merge |
| `setBoardInformationPlusAttributes($attrs)` | `ue-board-information-plus` | Iterates over `cards`, runs a connector per card, attaches `data` to each card |

## Connector Integration

When an attributes array contains `'connector' => 'Module|route^Type->method'`, the widget resolves it at render time:

```php
$data = init_connector($attributes['connector']);
// $data = ['items' => Collection, 'route' => '...', 'repository' => '...', 'module' => '...']
```

The resolved data is merged into the component's attribute array so the frontend receives pre-fetched records.

## Example

```php
use Unusualify\Modularity\Services\View\UWidget;

$widget = UWidget::makeVCol([
    'component'  => 'ue-table',
    'connector'  => 'Orders|index^Order->getLatest',
    'attributes' => ['headers' => ['id', 'total', 'status']],
]);

return Inertia::render('Dashboard', [
    'widget' => $widget->render(),
]);
```

The frontend receives a fully populated `items` array inside the `ue-table` attributes without any additional controller code.
