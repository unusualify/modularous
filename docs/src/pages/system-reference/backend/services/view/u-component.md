---
sidebarPos: 1
sidebarTitle: UComponent
---

# UComponent

**File**: `src/Services/View/UComponent.php`  
**Extends**: `Illuminate\View\Component`

`UComponent` is a fluent builder that constructs a PHP array schema representing a single Vue component. The schema is serialized to JSON by Inertia and rendered by the matching Vue component on the frontend.

## Properties

| Property | Type | Description |
|----------|------|-------------|
| `$tag` | `string` | Vue component tag name (e.g. `ue-form`, `v-row`) |
| `$attributes` | `array` | Component props/attributes |
| `$slots` | `array` | Named slot contents |
| `$directives` | `array` | Vue directives |
| `$elements` | `array\|null` | Child component schemas |

## Methods

| Method | Description |
|--------|-------------|
| `make()` | Static factory — returns a new `UComponent` instance |
| `makeComponent($tag, $attributes, $elements, $slots, $directives)` | Configure all properties in one call; returns `$this` |
| `setTag($tag)` | Set the component tag; returns `$this` |
| `setAttributes($attributes)` | Set props array (passes through `hydrateAttributes`); returns `$this` |
| `setSlots($slots)` | Set named slots; returns `$this` |
| `setDirectives($directives)` | Set Vue directives; returns `$this` |
| `setElements($elements)` | Set child elements (ignored when `''`); returns `$this` |
| `addChildren($element)` | Append a child (string, array, or `UComponent`); returns `$this` |
| `addSlot($slotName, $slotContent)` | Add a single named slot; returns `$this` |
| `render()` | Return the complete schema array |

## Magic Methods

`UComponent` intercepts both instance and static calls matching these patterns:

| Pattern | Example | Behaviour |
|---------|---------|-----------|
| `make{Tag}(...)` | `UComponent::makeUeForm($attrs)` | Calls `makeComponent('ue-form', ...)` |
| `make{Tag}(...)` | `UComponent::makeVRow()` | Calls `makeComponent('v-row', ...)` |
| `addChildren{Tag}(...)` | `->addChildrenUeInput(...)` | Calls `addChildren('ue-input', ...)` |

Tag names in method names are converted from StudlyCase to kebab-case automatically (`UeForm` → `ue-form`, `VRow` → `v-row`).

## render() Output

```php
[
    'tag'        => 'ue-form',
    'attributes' => ['model' => 'user', 'action' => '/users'],
    'slots'      => [],
    'directives' => [],
    'elements'   => [
        ['tag' => 'ue-input', 'attributes' => ['name' => 'email'], ...],
    ],
]
```

The `elements` key is only present when child components have been added.

## Example

```php
use Unusualify\Modularous\Services\View\UComponent;

$form = UComponent::makeUeForm(['model' => 'user'])
    ->addChildren(
        UComponent::makeUeInput(['name' => 'email', 'type' => 'email'])
    )
    ->addChildren(
        UComponent::makeUeInput(['name' => 'password', 'type' => 'password'])
    );

return Inertia::render('UserCreate', [
    'form' => $form->render(),
]);
```
