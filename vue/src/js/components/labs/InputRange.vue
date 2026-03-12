<template>
    <v-range-slider
        v-model="input"
        :label="obj.schema.label"
        v-bind="obj.schema"
    ></v-range-slider>
</template>

<script>
import { useInput, makeInputEmits } from '@/hooks'

export default {
  name: 'v-input-range',
  emits: [...makeInputEmits],
  setup (props, context) {
    return { ...useInput(props, context) }
  }
}
</script>
