---
sidebarTitle: useImage
---

# useImage

Manages the state for an image input backed by the media library. Identical API to `useFile` but defaults `mediaType` to `'image'` and opens the media library in image mode.

**File:** `vue/src/js/hooks/useImage.js`  
**Props factory:** `makeImageProps`

---

## Usage

```js
import { useImage, makeImageProps } from '@/hooks'

const props = defineProps({ ...makeImageProps() })
const {
  input,
  items,
  remainingItems,
  isDraggable,
  mediableActive,
  addLabel,
  deleteItem,
  deleteAll
} = useImage(props, context)
```

## Props (via `makeImageProps`)

Extends `makeInputProps` and `makeDraggableProps` plus:

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `mediaType` | `String` | `'image'` | Media type passed to the media library |
| `name` | `String` | required | Field name — media library slot key |
| `itemLabel` | `String` | `t('Image')` | Label used in the add button |
| `btnLabel` | `String` | `t('fields.medias.btn-label')` | Add image button label |
| `max` | `Number` | `1` | Maximum number of images |
| `disabled` | `Boolean` | `false` | Disable the input |
| `required` | `Boolean` | `false` | Mark as required |
| `hover` | `Boolean` | `false` | Enable hover preview |
| `isSlide` | `Boolean` | `false` | Render as a slideshow slot |
| `index` | `Number` | `0` | Index in a multi-context media slot |
| `mediaContext` | `String` | `''` | Media library context key (e.g. `'cover'`) |
| `activeCrop` | `Boolean` | `true` | Show crop tool in the media library |
| `widthMin` | `Number` | `0` | Minimum accepted image width in px |
| `heightMin` | `Number` | `0` | Minimum accepted image height in px |
| `note` | `String` | `''` | Note text |
| `fieldNote` | `String` | `''` | Field-level note |
| `filesizeMax` | `Number` | `0` | Maximum file size (0 = unlimited) |
| `buttonOnTop` | `Boolean` | `false` | Display add button above the image list |

## Returns

Same as [useFile](/system-reference/frontend/composables/use-file):

| Name | Type | Description |
|------|------|-------------|
| `input` | `Ref<Array>` | The current image list |
| `items` | `ComputedRef<Array>` | Images from the media library store |
| `remainingItems` | `ComputedRef<Number>` | `max - input.length` |
| `isDraggable` | `ComputedRef<Boolean>` | True when draggable and more than one image |
| `mediableActive` | `Ref<Boolean>` | Controls media library selection application |
| `addLabel` | `ComputedRef<String>` | Localised add button label |
| `deleteItem` | `(index) => void` | Remove image at index |
| `deleteAll` | `() => void` | Clear all images |

## See Also

- [useFile](/system-reference/frontend/composables/use-file) — identical hook for non-image files
- [useDraggable](/system-reference/frontend/composables/use-draggable) — drag-and-drop props
