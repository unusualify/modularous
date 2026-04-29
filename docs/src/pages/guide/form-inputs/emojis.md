---
sidebarTitle: Emojis
sidebarPos: 14
---

# Emojis

`VInputEmojis` is an emoji picker dialog. It renders a grid of ~180 emoji buttons inside a `v-dialog` and emits the selected emoji as a string. The dialog visibility is controlled via `v-model`. There is no corresponding PHP hydrate — this component is used directly in Vue templates.

## Vue Component

**Registered as:** `VInputEmojis`
**File:** `vue/src/js/components/inputs/Emojis.vue`

### Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `modelValue` | `Boolean` | `false` | Controls dialog visibility (`true` = open) |
| `disabled` | `Boolean` | `false` | Reserved; currently does not suppress the dialog |

### Emits

| Event | Payload | Description |
|-------|---------|-------------|
| `update:modelValue` | `Boolean` | Dialog open/close state change |
| `emoji-selected` | `String` | The emoji character the user clicked |

### Usage

```vue
<script setup>
import { ref } from 'vue'

const pickerOpen = ref(false)
const message = ref('')

function onEmojiSelected(emoji) {
  message.value += emoji
}
</script>

<template>
  <v-btn @click="pickerOpen = true">Pick Emoji</v-btn>

  <VInputEmojis
    v-model="pickerOpen"
    @emoji-selected="onEmojiSelected"
  />
</template>
```

### Behaviour

- The picker renders a scrollable `8-column` grid (max height 300 px).
- Selecting an emoji emits `emoji-selected` and immediately closes the dialog.
- The built-in emoji set covers faces, hand gestures, hearts, symbols, and more (~180 entries).

## See Also

- [Chat](/guide/form-inputs/input-chat) — Chat input that uses emoji picker integration
- [Forms overview](/guide/form-inputs/overview) — Schema-driven form architecture
