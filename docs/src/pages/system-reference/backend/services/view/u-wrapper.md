---
sidebarPos: 3
sidebarTitle: UWrapper
---

# UWrapper

**File**: `src/Services/View/UWrapper.php`

`UWrapper` is a **static factory** for building grid-layout wrapper schemas. It composes `UComponent` instances into `v-row` / `v-col` structures without requiring instantiation.

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `make()` | `static make(): self` | Returns a new instance (rarely needed) |
| `makeGridSection` | `static makeGridSection($elements, $rowAttributes, $colAttributes): array` | Wraps an array of components in a `v-row` with one `v-col` per element |
| `makeFormWrapper` | `static makeFormWrapper($forms): array` | Converts an array of form attribute arrays into a grid of `ue-form` components |
| `makeProfileWrapper` | `static makeProfileWrapper($elements, $attributes)` | Stub — not yet implemented |

## makeGridSection

Produces a `v-row` → `v-col[]` schema. Each element in `$elements` becomes one column.

```php
$grid = UWrapper::makeGridSection(
    elements: [$componentA, $componentB],
    rowAttributes: ['class' => 'my-4'],
    colAttributes: ['cols' => 12, 'lg' => 6],
);
```

**Element types accepted:**

| Type | Behaviour |
|------|-----------|
| `UComponent` instance | Appended directly as a child of the column |
| Associative array with `content` key | Contents split across the column; `parent_attributes` merged into col attributes |
| Plain array with multiple items | Each item appended as a child of the same column |
| Plain array with one item | That item appended as the column's child |

Default column attributes: `['class' => '', 'cols' => 12, 'lg' => 6]` — merged with `$colAttributes`.

## makeFormWrapper

Shorthand for rendering a list of form schemas as a responsive grid:

```php
$layout = UWrapper::makeFormWrapper([
    ['model' => 'profile', 'action' => '/profile'],
    ['model' => 'password', 'action' => '/password'],
]);
```

Internally calls `makeGridSection()` after wrapping each element in `UComponent::makeUeForm()`.

## Example

```php
use Unusualify\Modularous\Services\View\UComponent;
use Unusualify\Modularous\Services\View\UWrapper;

$grid = UWrapper::makeGridSection([
    UComponent::makeUeCard(['title' => 'Revenue']),
    UComponent::makeUeCard(['title' => 'Users']),
    UComponent::makeUeCard(['title' => 'Orders']),
], [], ['cols' => 12, 'lg' => 4]);

return Inertia::render('Dashboard', ['grid' => $grid]);
```
