<template>
  <div class="generic-form">
    <ue-form
      title-placement="outside"
      v-bind="formAttributes"
    />
  </div>
</template>

<script setup>
import { h } from 'vue'
import MainLayout from '@/Pages/Layouts/MainLayout.vue'

defineProps({
  formAttributes: {
    type: Object,
    default: () => ({}),
  }
})

defineOptions({
  layout: (h, page) => h(MainLayout, () => page),
  name: 'GenericForm',
})
</script>

<style scoped>
.generic-form {
  display: flex;
  flex-direction: column;
  flex: 1 1 auto;
  min-height: 0;
}
</style>
