<template>
  <div class="generic-form">
    <div v-if="pageTitle" class="generic-form__header pb-4">
      <ue-title v-bind="titleAttributes" :text="pageTitle" />
      <ue-title v-if="pageSubtitle" v-bind="subtitleAttributes" :text="pageSubtitle" />
    </div>
    <ue-form
      v-bind="formAttributes"
      :title-placement="pageTitle ? 'outside' : 'inside'"
    />
  </div>
</template>

<script setup>
import { computed, h } from 'vue'
import { useI18n } from 'vue-i18n'
import MainLayout from '@/Pages/Layouts/MainLayout.vue'
import {
  resolveTableTitleAttributes,
  resolveTableSubtitleAttributes,
} from '@/hooks/table/tableTitleAttributes'

const props = defineProps({
  formAttributes: {
    type: Object,
    default: () => ({}),
  }
})

const { t, te } = useI18n()

const translate = (text) => (te(text) ? t(text) : text)

const pageTitle = computed(() => {
  const { title, noTitle } = props.formAttributes
  if (noTitle || !title) return null

  return translate(window.__isObject(title) ? title.text : title)
})

const pageSubtitle = computed(() => (props.formAttributes.subtitle ? translate(props.formAttributes.subtitle) : null))

const titleAttributes = resolveTableTitleAttributes('outside')
const subtitleAttributes = resolveTableSubtitleAttributes('outside')

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
