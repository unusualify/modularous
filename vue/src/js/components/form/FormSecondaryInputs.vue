<script setup>
import { computed } from 'vue'
import { cloneDeep } from 'lodash-es'

const props = defineProps({
  inputs: {
    type: Array,
    required: true,
  },
  modelValue: {
    type: Object,
    required: true,
  },
  formItem: {
    type: Object,
    default: () => ({}),
  },
})

const emit = defineEmits(['update:modelValue'])

const input = computed({
  get() {
    return props.modelValue ?? {}
  },
  set(value) {
    emit('update:modelValue', value)
  },
})

const schema = computed(() => {
  return props.inputs.reduce((acc, field, index) => {
    const key = field?.name ?? `secondary-${index}`
    const inputField = cloneDeep(field)
    inputField.col = {
      cols: 12,
    }
    inputField.hideDetails = 'auto'
    acc[key] = inputField
    return acc
  }, {})
})

defineOptions({
  name: 'FormSecondaryInputs',
})
</script>

<template>
  <v-custom-form-base
    v-if="inputs && inputs.length"
    v-model="input"
    :schema="schema"
    :form-item="formItem"
    no-auto-generate-schema
  />
</template>
