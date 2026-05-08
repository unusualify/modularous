---
sidebarTitle: useInputHandlers
---

# useInputHandlers

Provides slot-driven click handler dispatch for form inputs. When a schema field declares `slotHandlers`, clicking an input slot (e.g. an append icon) resolves the matching handler by name and invokes it.

**File:** `vue/src/js/hooks/useInputHandlers.js`

---

## Usage

```js
import { useInputHandlers } from '@/hooks'

const { invokeInputClickHandler } = useInputHandlers()

// Called from a slot click event in FormBaseField
invokeInputClickHandler(obj, 'append-inner')
```

## Returns

| Name | Signature | Description |
|------|-----------|-------------|
| `invokeInputClickHandler` | `(obj, slotName) => void` | Looks up `obj.schema.slotHandlers[camelCase(slotName)]` and calls the matching `{name}Handler` method |
| `passwordHandler` | `(obj, slotName) => void` | Built-in handler: toggles `obj.schema.type` between `'password'` and `'text'`, and swaps the append icon |

## Built-in handlers

| Handler name | Triggered by schema key | Effect |
|--------------|------------------------|--------|
| `password` | `slotHandlers: { appendInner: 'password' }` | Toggles password visibility and icon |

## Adding a handler in schema config

```php
// In your module's input config
[
    'type'  => 'password',
    'name'  => 'password',
    'label' => 'Password',
    'slotHandlers' => [
        'appendInner' => 'password',   // camelCase slot name → handler name
    ],
    'appendInnerIcon' => '$visibility',
]
```

## How resolution works

```
slotName 'append-inner'
  → camelCase → 'appendInner'
  → look up obj.schema.slotHandlers.appendInner  → 'password'
  → camelCase → 'password'
  → call passwordHandler(obj, 'appendInner')
```

## Notes

- This hook is a low-level primitive called by `FormBaseField`. You rarely need it directly.
- Add custom handlers by extending the `methods` reactive object in your own composable that wraps `useInputHandlers`.
