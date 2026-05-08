---
sidebarTitle: useFile
---

# useFile

Manages the state for a file input that is backed by the media library. Handles selection sync from the Vuex `mediaLibrary` store, drag-and-drop reorder, and item deletion.

**File:** `vue/src/js/hooks/useFile.js`  
**Props factory:** `makeFileProps`

---

## Usage

```js
import { useFile, makeFileProps } from '@/hooks'

const props = defineProps({ ...makeFileProps() })
const {
  input,
  items,
  remainingItems,
  isDraggable,
  mediableActive,
  addLabel,
  deleteItem,
  deleteAll
} = useFile(props, context)
```

## Props (via `makeFileProps`)

Extends `makeInputProps` and `makeDraggableProps` plus:

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `mediaType` | `String` | `'file'` | Media type sent to the media library |
| `name` | `String` | required | Field name — used as the media library slot key |
| `itemLabel` | `String` | `t('File')` | Label used in the add button |
| `endpoint` | `String` | `''` | Upload endpoint |
| `max` | `Number` | `1` | Maximum number of files |
| `note` | `String` | `''` | Note text displayed in the input |
| `fieldNote` | `String` | `''` | Field-level note |
| `filesizeMax` | `Number` | `0` | Maximum file size in bytes (0 = unlimited) |
| `buttonOnTop` | `Boolean` | `false` | Display the add button above the file list |

## Returns

| Name | Type | Description |
|------|------|-------------|
| `input` | `Ref<Array>` | The current file list (synced with `modelValue`) |
| `items` | `ComputedRef<Array>` | Files selected via the media library store |
| `remainingItems` | `ComputedRef<Number>` | `max - input.length` |
| `isDraggable` | `ComputedRef<Boolean>` | True when `draggable` and more than one file |
| `mediableActive` | `Ref<Boolean>` | Controls whether media library selections are applied |
| `addLabel` | `ComputedRef<String>` | Localised add button label |
| `deleteItem` | `(index) => void` | Remove file at `index` |
| `deleteAll` | `() => void` | Clear all files |
| _(all useInput returns)_ | | `id`, `updateModelValue`, `isEditing`, etc. |

## Sync behaviour

The hook watches three sources and keeps them in sync:

1. `store.state.mediaLibrary.selected[props.name]` — when the media library inserts files
2. `states.input` — emits `update:modelValue` on change
3. `props.modelValue` — external model updates are reflected in `states.input`

## See Also

- [useImage](/system-reference/frontend/composables/use-image) — identical API for image inputs
- [useDraggable](/system-reference/frontend/composables/use-draggable) — drag-and-drop props
- [File storage with FilePond](/guide/generics/file-storage-with-filepond)
