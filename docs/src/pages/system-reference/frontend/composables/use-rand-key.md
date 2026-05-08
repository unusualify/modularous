---
sidebarTitle: useRandKey
---

# useRandKey

Returns a unique integer key for the component instance, based on `Date.now()` combined with a random number. Use it as a `:key` binding to force a component to re-mount.

**File:** `vue/src/js/hooks/useRandKey.js`

---

## Usage

```js
import { useRandKey } from '@/hooks'

const key = useRandKey()
```

```html
<!-- Force re-mount when the schema changes -->
<form-base :key="key" :schema="schema" />
```

## Returns

A `Number` — `Date.now() + Math.floor(Math.random() * 9999)`.

## Notes

- This is not reactive — it returns a plain number, not a `ref`. Call it once per component instance.
- If you need a reactive key that changes on demand, wrap it in a `ref` and call `useRandKey()` when you need to reset:

```js
const formKey = ref(useRandKey())

function resetForm() {
  formKey.value = useRandKey()
}
```
