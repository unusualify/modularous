<!--
  InputRadio - Schema type for radio group.
  Replaces inline radio block in CustomFormBase.
  Receives options, modelValue, and bindSchema props.
-->
<template>
  <v-radio-group
    v-bind="$attrs"
    :model-value="modelValue"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <v-radio
      v-for="(option, idx) in normalizedOptions"
      :key="idx"
      v-bind="boundOption(option)"
    >
      <template v-for="(_, name) in $slots" #[name]="slotData">
        <slot :name="name" v-bind="{ id, obj, index, idx, option, ...slotData }" />
      </template>
    </v-radio>
  </v-radio-group>
</template>

<script>
import { computed } from 'vue'
import { isString } from '@/utils/helpers'

export default {
  name: 'InputRadio',
  inheritAttrs: true,
  props: {
    modelValue: [String, Number, Boolean],
    options: {
      type: Array,
      default: () => []
    },
    id: String,
    obj: Object,
    index: [Number, String]
  },
  emits: ['update:modelValue'],
  setup (props) {
    const normalizedOptions = computed(() => props.options ?? [])
    const boundOption = (b) => (isString(b) ? { value: b, label: b } : b)
    return { normalizedOptions, boundOption }
  }
}
</script>
