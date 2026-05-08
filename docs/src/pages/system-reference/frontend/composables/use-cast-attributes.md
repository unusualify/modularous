---
sidebarTitle: useCastAttributes
---

# useCastAttributes

Resolves dynamic attribute patterns in action config strings. Supports three syntaxes for binding item values into labels, URLs, or expressions without writing custom Vue logic.

**File:** `vue/src/js/hooks/useCastAttributes.js`

---

## Syntax Reference

| Pattern | Example | Resolves to |
|---------|---------|-------------|
| Dot-notation `$key.sub` | `$user.name` | `item.user.name` |
| Wildcard `$items.*.title` | `$items.*.title` | Joined values of the `title` key from all items in the array |
| Eval `$(expr)$` | `$($price * 1.18)$` | Result of evaluated JS expression (item values substituted first) |

---

## Usage

```js
import { useCastAttributes } from '@/hooks'

const { castAttribute, castObjectAttributes } = useCastAttributes()

const item = { id: 5, user: { name: 'Alice' }, price: 100 }

// Single attribute
castAttribute('$user.name', item)          // => 'Alice'
castAttribute('$( $price * 1.18 )$', item) // => 118

// Deep object (all string values in the object are cast)
castObjectAttributes({ label: 'Hello $user.name', value: '$id' }, item)
// => { label: 'Hello Alice', value: '5' }
```

## Returns

| Name | Signature | Description |
|------|-----------|-------------|
| `matchAttribute` | `(value) => Boolean` | True if value contains a dot-notation pattern |
| `matchStandardAttribute` | `(value) => Boolean` | True if value matches `$key` |
| `matchEvalAttribute` | `(value) => Boolean` | True if value matches `$(...)$` |
| `matchAnyPattern` | `(value) => Boolean` | True if any pattern matches |
| `castAttribute` | `(value, item) => any` | Cast a single value against an item |
| `castStandardAttribute` | `(value, item, options?) => any` | Cast a `$key` pattern; supports `clearAsterisk` option |
| `castEvalAttribute` | `(value, item) => any` | Evaluate a `$(expr)$` expression |
| `castObjectAttribute` | `(value, item, options?) => any` | Cast a single string value |
| `castObjectAttributes` | `(data, item) => any` | Recursively cast all string values in an object or array |

## Where it is used

`useItemActions` calls `castObjectAttributes(action, editingItem)` before rendering each action button, allowing action labels and endpoint URLs to reference the current row item:

```php
// In module config
'actions' => [
    [
        'type'     => 'blank',
        'label'    => 'View $name',
        'endpoint' => '/preview/:id',
    ]
]
```

## See Also

- [useItemActions](/system-reference/frontend/composables/use-item-actions) — main consumer of `castObjectAttributes`
