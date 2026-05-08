---
sidebarTitle: useMediaLibrary
---

# useMediaLibrary

Opens the media library picker modal by committing the required Vuex mutations, allowing any component to trigger the media picker for a specific field.

**File:** `vue/src/js/hooks/useMediaLibrary.js`

---

## Usage

```js
import { useMediaLibrary } from '@/hooks'

const { openMediaLibrary } = useMediaLibrary()

// Open the library to pick up to 3 images for the "gallery" field at index 0
openMediaLibrary(3, 'gallery', 0, existingItems)
```

## Returns

| Name | Signature | Description |
|------|-----------|-------------|
| `openMediaLibrary` | `(max, name, index?, initialItems?) => void` | Open the media library modal configured for a specific field |

### `openMediaLibrary` Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `max` | `Number` | — | Maximum number of items that can be selected |
| `name` | `String` | — | The form field name this picker is bound to |
| `index` | `Number` | `null` | Repeater row index, when the field is inside a repeater |
| `initialItems` | `Array` | `[]` | Items already attached to the field, shown as pre-selected |

## Behavior

`openMediaLibrary` commits 8 Vuex mutations in sequence:

1. Sets `mediaLibrary.open` to `true`
2. Sets `mediaLibrary.max` (max selectable items)
3. Sets `mediaLibrary.name` (target field name)
4. Sets `mediaLibrary.index` (repeater index)
5. Sets `mediaLibrary.initialItems` (pre-selected items)
6. Resets the current selection to `initialItems`
7. Clears any previous upload state
8. Resets the picker's page to 1

## Notes

- The media library is a global modal; only one picker can be open at a time.
- When the user confirms their selection, the Vuex store emits an event that `useFile` / `useImage` listen to in order to update the form field value.

## See Also

- [useMediaItems](/system-reference/frontend/composables/use-media-items) — selection state inside the picker
- [useFile](/system-reference/frontend/composables/use-file) — file field input state
- [useImage](/system-reference/frontend/composables/use-image) — image field input state
