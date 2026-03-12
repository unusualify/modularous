<!--
  InputTitle - Schema type for display-only title/heading.
  Replaces inline title block in CustomFormBase.
  Receives bindSchema(obj) props: title, text, label, name, etc.
-->
<template>
  <ue-title
    :text="displayText"
    v-bind="$attrs"
  />
</template>

<script>
import { computed } from 'vue'

export default {
  name: 'InputTitle',
  inheritAttrs: true,
  props: {
    title: String,
    text: String,
    label: String,
    name: String
  },
  setup (props) {
    const displayText = computed(() =>
      props.title ?? props.text ?? props.label ?? props.name ?? ''
    )
    return { displayText }
  }
}
</script>
