---
sidebarTitle: useFilepond
---

# useFilepond

Sets up FilePond upload props and derives validation rules from the component's schema and prop values. Used internally by `VInputFilepond` and `VInputFilepondAvatar`.

**File:** `vue/src/js/hooks/useFilepond.js`  
**Props factory:** `makeFilepondProps`

---

## Usage

```js
import { useFilepond, makeFilepondProps } from '@/hooks'

const props = defineProps({ ...makeFilepondProps() })
const { filepondRules, max } = useFilepond(props, context)
```

```html
<file-pond
  :max-files="max"
  :rules="filepondRules"
  ...
/>
```

## Props (via `makeFilepondProps`)

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `maxFiles` | `Number` | `2` | Maximum number of files |
| `min` | `Number` | — | Minimum required files |
| `rules` | `Array` | `[]` | Additional validation rules |
| `noRules` | `Boolean` | `false` | Skip rule injection |
| `endPoints` | `Object` | `{}` | FilePond server endpoint config |
| `acceptedFileTypes` | `String` | `''` | Comma-separated MIME types |
| `allowImagePreview` | `Boolean` | `false` | Enable image preview plugin |
| `allowMultiple` | `Boolean` | `false` | Allow multiple file uploads |
| `allowProcess` | `Boolean` | `true` | Auto-upload on file add |
| `allowRemove` | `Boolean` | `true` | Allow file removal |
| `allowDrop` | `Boolean` | `true` | Allow drag-to-drop upload |
| `allowReorder` | `Boolean` | `false` | Allow drag to reorder files |
| `allowReplace` | `Boolean` | `false` | Replace file on re-upload |
| `allowFileSizeValidation` | `Boolean` | `true` | Enable file size validation |
| `maxFileSize` | `String` | `'5MB'` | Maximum single file size |
| `minFileSize` | `String` | `'1KB'` | Minimum single file size |
| `maxTotalFileSize` | `String` | `null` | Maximum total upload size |
| `hint` | `String` | `null` | Helper text |
| `hideDetails` | `Boolean` | `false` | Hide error/hint details |
| `disabled` | `Boolean` | `false` | Disable the input |
| `labelWeight` | `String` | `'regular'` | Label font weight |
| `subtitle` | `String` | `null` | Subtitle text |
| `subtitleWeight` | `String` | `'thin'` | Subtitle font weight |
| `hintWeight` | `String` | `'thin'` | Hint font weight |

## Returns

| Name | Type | Description |
|------|------|-------------|
| `filepondRules` | `Ref<Array>` | Computed validation rule array (includes a required rule if `min` is set and the input is creatable/editable) |
| `max` | `Ref<Number>` | Effective max files (clamped to be ≥ `min`, minimum `5` if zero) |

## Rule injection logic

When `min > 0` and `noRules` is `false` and the input is in create/edit mode, a `required:array:{min}` rule is automatically prepended to `filepondRules`.

## See Also

- [File storage with FilePond](/guide/generics/file-storage-with-filepond)
- [useFile](/system-reference/frontend/composables/use-file) — media-library file input
- [input-filepond-avatar](/guide/form-inputs/input-filepond-avatar) — avatar upload component
