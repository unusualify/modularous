<template>
  <div class="ue-stepper-preview">
    <div class="mb-4">
      <h2 class="ue-stepper-preview__heading">{{ previewTitle ?? $t('Review and Confirm Your Order') }}</h2>
      <p class="ue-stepper-preview__subheading">{{ previewSubtitle ?? $t('Please review your press release details.') }}</p>
    </div>

    <v-row dense>
      <template v-for="(context, index) in previewCards" :key="`summary-${index}`">
        <!-- Compact cards (e.g. country/package) -->
        <v-col v-if="!context.wide" cols="12" :md="context.col" class="d-flex">
          <div class="ue-stepper-preview__card ue-stepper-preview__card--compact w-100">
            <div class="d-flex align-center justify-space-between ga-2">
              <span class="ue-stepper-preview__card-title">{{ context.title }}</span>
              <v-chip v-if="context.badge" size="small" color="primary" variant="tonal" label class="ue-stepper-preview__badge">
                {{ context.badge }}
              </v-chip>
            </div>
            <div v-for="field in context.fields" :key="field.label" class="ue-stepper-preview__inline-field">
              <span class="font-weight-medium">{{ field.label }}:</span> {{ field.value }}
            </div>
          </div>
        </v-col>

        <!-- Wide cards (e.g. content) -->
        <v-col v-else cols="12">
          <div class="ue-stepper-preview__section-title">{{ context.title }}</div>
          <div class="ue-stepper-preview__card">
            <v-row dense>
              <v-col v-for="field in context.fields" :key="field.label" cols="12" sm="6" class="py-2">
                <div class="ue-stepper-preview__label">{{ field.label }}</div>
                <div class="ue-stepper-preview__value">{{ field.value }}</div>
              </v-col>
            </v-row>
          </div>
        </v-col>
      </template>

      <!-- Final form data -->
      <v-col cols="12" v-if="previewFormData.length > 0">
        <div class="ue-stepper-preview__section-title mb-0">{{ finalFormTitle }}</div>
        <div v-if="finalFormSubtitle" class="ue-stepper-preview__subheading mb-3">{{ finalFormSubtitle }}</div>

        <div
          v-for="(data, index) in previewFormData"
          :key="`final-form-data-${index}`"
          :class="[
            'ue-stepper-preview__card ue-stepper-preview__addon mb-3',
            { 'ue-stepper-preview__addon--selected': fieldSelected(data) }
          ]"
        >
          <div class="d-flex ga-4">
            <div class="flex-grow-1 min-w-0">
              <div class="ue-stepper-preview__addon-name">{{ cardItems(data).name }}</div>
              <p v-if="cardItems(data).description" class="ue-stepper-preview__addon-description" v-html="cardItems(data).description"></p>
              <div v-if="cardItems(data).tags.length" class="d-flex ga-2 flex-wrap mt-3">
                <v-chip v-for="tag in cardItems(data).tags" :key="tag.id ?? tag" size="small" label class="ue-stepper-preview__tag">
                  {{ tag.name ?? tag.slug ?? tag }}
                </v-chip>
              </div>
            </div>
            <div class="d-flex flex-column align-end justify-space-between flex-shrink-0 ga-3">
              <div class="ue-stepper-preview__addon-price">{{ cardItems(data).price }}</div>
              <v-btn
                size="small"
                rounded="pill"
                class="text-none"
                color="primary"
                :variant="fieldSelected(data) ? 'outlined' : 'flat'"
                :prepend-icon="fieldSelected(data) ? 'mdi-minus-circle-outline' : 'mdi-plus-circle-outline'"
                :readonly="isReadOnly(data)"
                @click="$emit('final-form-action', {
                  index: index,
                  event: !fieldSelected(data)
                })"
              >
                {{ fieldSelected(data) ? $t('Remove') : $t('Add') }}
              </v-btn>
            </div>
          </div>
        </div>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import { isString, isObject, includes } from 'lodash-es'

export default {
  name: 'StepperPreview',
  emits: ['final-form-action'],
  props: {
    formattedPreview: {
      type: Array,
      default: () => []
    },
    previewFormData: {
      type: Array,
      default: () => []
    },
    lastStepModel: {
      type: Object,
      required: true
    },
    previewTitle: {
      type: String,
      default: null
    },
    previewSubtitle: {
      type: String,
      default: null
    },
    finalFormTitle: {
      type: String,
      required: true
    },
    finalFormSubtitle: {
      type: String,
      default: null
    },
    protectedLastStepModel: {
      type: Object,
      default: () => {}
    }
  },
  computed: {
    previewCards() {
      return this.formattedPreview.map((context) => {
        const col = context.col || 6
        const wide = col >= 12
        const entries = Object.values(context.items ?? {})
          .flatMap((section) => Object.entries(section ?? {}))

        let badge = null
        if (!wide && entries.length > 0 && !Array.isArray(entries[0][1]) && !isObject(entries[0][1])) {
          badge = entries.shift()[1]
        }

        const fields = entries
          .map(([label, value]) => ({ label: label.trim(), value: this.formatValue(value) }))
          .filter((field) => field.value !== '')

        return { title: context.title, col, wide, badge, fields }
      })
    }
  },
  methods: {
    formatValue(value) {
      if (Array.isArray(value)) {
        return value.map((item) => (isObject(item) ? item.name ?? item.title ?? item.file_name ?? '' : item)).filter(Boolean).join(', ')
      }
      if (isObject(value)) {
        return value.name ?? value.title ?? ''
      }
      return value ?? ''
    },
    cardItems(data) {
      const [[name, description, tags] = [], price] = data.form_card_items || [
        [data.name, data.description, data.tags],
        data.base_price_without_vat_formatted
      ]

      return {
        name: name || 'N/A',
        description,
        tags: Array.isArray(tags) ? tags : [],
        price: price || 'N/A'
      }
    },
    fieldSelected(data) {
      const fieldName = data.fieldName
      const values = this.lastStepModel[fieldName] ?? []
      const fieldFormat = data._fieldFormat ?? 'id'
      const fieldFormatUniqueKey = data._fieldFormatUniqueKey ?? 'id'
      const fieldFormatSourceKey = data._fieldFormatSourceKey ?? 'id'

      let selected = false
      if(isString(fieldFormat)){
        selected = includes(values, data[fieldFormat])
      }else if(isObject(fieldFormat)){
        let item = values.find(item => item[fieldFormatUniqueKey] === data[fieldFormatSourceKey])
        selected = item !== undefined && item !== null
      }

      return selected
    },
    isReadOnly(data) {
      const fieldName = data.fieldName
      const values = this.protectedLastStepModel[fieldName] ?? []
      const fieldFormat = data._fieldFormat ?? 'id'
      const fieldFormatUniqueKey = data._fieldFormatUniqueKey ?? 'id'
      const fieldFormatSourceKey = data._fieldFormatSourceKey ?? 'id'
      let exists = false

      if(isString(fieldFormat)){
        exists = includes(values, data[fieldFormat])
      }else if(isObject(fieldFormat)){
        let item = values.find(item => __isset(data[fieldFormatSourceKey])
          && __isset(item[fieldFormatUniqueKey])
          && item[fieldFormatUniqueKey] === data[fieldFormatSourceKey])
        exists = item !== undefined && item !== null
      }

      return exists
    }
  }
}
</script>

<style lang="sass">
  .ue-stepper-preview
    $border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity))

    &__heading
      font-size: 1.25rem
      font-weight: 500
      line-height: 1.4
      color: rgb(var(--v-theme-primary-darken-3))

    &__subheading
      font-size: 0.8125rem
      color: rgba(var(--v-theme-on-surface), 0.7)

    &__section-title
      font-size: 0.9375rem
      font-weight: 600
      color: rgb(var(--v-theme-primary-darken-3))
      margin: 8px 0 12px

    &__card
      background: rgb(var(--v-theme-surface))
      border: $border
      border-radius: 12px
      padding: 16px 20px
      transition: background-color 0.2s ease, border-color 0.2s ease

      &--compact
        padding: 12px 16px

    &__card-title
      font-size: 0.8125rem
      font-weight: 600
      text-transform: uppercase
      color: rgb(var(--v-theme-primary-darken-3))

    &__badge
      font-size: 0.6875rem !important
      font-weight: 500
      height: 20px !important

    &__inline-field
      margin-top: 6px
      font-size: 0.75rem
      color: rgb(var(--v-theme-primary-darken-3))

    &__label
      font-size: 0.75rem
      color: rgba(var(--v-theme-on-surface), 0.6)

    &__value
      margin-top: 2px
      font-size: 0.875rem
      color: rgb(var(--v-theme-primary-darken-3))
      word-break: break-word

    &__addon
      &--selected
        background: rgba(var(--v-theme-primary), 0.08)
        border-color: rgb(var(--v-theme-primary))

    &__addon-name
      font-size: 0.875rem
      font-weight: 600
      color: rgb(var(--v-theme-primary-darken-3))

    &__addon-description
      margin-top: 8px
      font-size: 0.8125rem
      line-height: 1.5
      color: rgba(var(--v-theme-on-surface), 0.7)
      white-space: pre-line
      word-wrap: break-word

    &__addon-price
      font-size: 1.375rem
      font-weight: 500
      color: rgb(var(--v-theme-primary-darken-3))

    &__tag
      background: rgba(var(--v-theme-primary), 0.08) !important
      color: rgb(var(--v-theme-primary-darken-3)) !important
      font-size: 0.6875rem !important
</style>
