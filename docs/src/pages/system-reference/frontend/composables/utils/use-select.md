---
sidebarTitle: useSelect
---

# useSelect

Provides a `makeSelectProps` factory defining the standard prop set for select-type inputs — items, value/title keys, multiple selection, and object return behavior.

**File:** `vue/src/js/hooks/utils/useSelect.js`

---

## Props Factory

```js
import { makeSelectProps } from '@/hooks/utils/useSelect'
```

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `itemValue` | `String` | `'id'` | The item property used as the option value |
| `itemTitle` | `String` | `'name'` | The item property used as the option label |
| `multiple` | `Boolean` | `false` | Allow multiple selections |
| `items` | `Array` | `[]` | The list of selectable options |
| `returnObject` | `Boolean` | `false` | When `true`, emit the full object instead of just `itemValue` |
| `objectIdDefiner` | `String` | — | Property that uniquely identifies an object when `returnObject` is `true` |
| `convertObject` | `Boolean` | `false` | Auto-convert object values to ID values on emit |
| `objectModelValues` | `Array` | `['*']` | Which object properties to include when `convertObject` is `true`. `['*']` means all. |
| `max` | `Number` | `null` | Maximum number of selectable items (for multiple mode) |

## Usage

```js
import useSelect, { makeSelectProps } from '@/hooks/utils/useSelect'

const props = defineProps({
  ...makeSelectProps(),
  // additional props
})

useSelect(props, context)
```

## Notes

- `makeSelectProps` is used by all select-type input components (`InputSelect`, `InputSelectScroll`, etc.) to share a consistent prop API.
- `returnObject` / `convertObject` / `objectModelValues` control the shape of emitted values when options are objects — allowing fine-grained control over what gets stored in the form model.

## See Also

- [useInputFetch](/system-reference/frontend/composables/use-input-fetch) — remote data loading for select inputs
- [useInput](/system-reference/frontend/composables/use-input) — base input composable used alongside `useSelect`
