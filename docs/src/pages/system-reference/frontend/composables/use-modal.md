---
sidebarTitle: useModal
---

# useModal

Manages modal open/close state, width, fullscreen toggle, and action callbacks. Used by the `UeModal` component and any component that wraps a Vuetify `v-dialog`.

**File:** `vue/src/js/hooks/useModal.js`  
**Props factories:** `makeModalProps`, `makeModalMediaProps`

---

## Usage

```js
import { useModal, makeModalProps } from '@/hooks'

const props = defineProps({ ...makeModalProps() })
const { dialog, openModal, closeModal, toggleModal, modalWidth } = useModal(props, context)
```

```html
<v-dialog v-model="dialog" :width="modalWidth">
  <slot />
  <template #actions>
    <v-btn @click="closeModal">Cancel</v-btn>
    <v-btn @click="confirmCallback?.()">Confirm</v-btn>
  </template>
</v-dialog>
```

## Props (via `makeModalProps`)

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `modelValue` | `Boolean` | — | External open state (`v-model`) |
| `useModelValue` | `Boolean` | `true` | When `false`, the modal manages its own `internalOpen` state |
| `title` | `String` | `null` | Modal title |
| `widthType` | `String` | `'md'` | `'xs'` \| `'sm'` \| `'md'` \| `'lg'` \| `'xl'` |
| `fullscreen` | `Boolean` | `false` | Open in full screen |
| `hasCloseButton` | `Boolean` | `false` | Show ✕ button in title bar |
| `hasFullscreenButton` | `Boolean` | `false` | Show fullscreen toggle |
| `hasTitleDivider` | `Boolean` | `false` | Divider below title |
| `noDefaultBodyPadding` | `Boolean` | `false` | Remove default body padding |
| `noActions` | `Boolean` | `false` | Hide the actions footer |
| `noCancelButton` | `Boolean` | `false` | Hide cancel button |
| `noConfirmButton` | `Boolean` | `false` | Hide confirm button |
| `description` | `String` | `null` | Description text |
| `cancelText` | `String` | `''` | Cancel button label |
| `confirmText` | `String` | `''` | Confirm button label |
| `confirmCallback` | `Function` | — | Called on confirm click |
| `rejectCallback` | `Function` | — | Called on cancel click |
| `confirmClosing` | `Boolean` | `true` | Close on confirm |
| `rejectClosing` | `Boolean` | `true` | Close on cancel |
| `transition` | `String` | `'bottom'` | Dialog transition type |

### Width presets

| `widthType` | Width |
|-------------|-------|
| `xs` | 320px |
| `sm` | 480px |
| `md` | 720px |
| `lg` | 1080px |
| `xl` | 1600px |

## Returns

| Name | Type | Description |
|------|------|-------------|
| `dialog` | `ComputedRef<Boolean>` | Two-way binding — reads `modelValue` (or `internalOpen`), writes via `emit` |
| `full` | `Ref<Boolean>` | Fullscreen toggle state |
| `modalWidth` | `ComputedRef<String\|null>` | Resolved pixel width string, or `null` in fullscreen |
| `openModal` | `() => Boolean` | Sets `dialog` to `true` |
| `closeModal` | `() => Boolean` | Sets `dialog` to `false` |
| `toggleModal` | `() => Boolean` | Toggles `dialog` |
| `emitModelValue` | `(val) => void` | Emits `update:modelValue` |
| `emitOpened` | `() => void` | Emits `opened` event |
| `clickOutside` | `(event) => void` | Emits `click:outside` event |

## `makeModalMediaProps`

A secondary props factory for modal wrappers that embed the media library:

| Prop | Type | Default |
|------|------|---------|
| `modalTitlePrefix` | `String` | `t('media-library.title')` |
| `btnLabelSingle` | `String` | `t('media-library.insert')` |
| `btnLabelUpdate` | `String` | `t('media-library.update')` |
| `btnLabelMulti` | `String` | `t('media-library.insert')` |

## See Also

- [useDynamicModal](/system-reference/frontend/composables/use-dynamic-modal) — inject-based global modal (no local props needed)
