<template>
  <div class="ue-form-summary-item">
    <!-- Step title -->
    <div class="d-flex align-center ga-2 mb-1">
      <v-avatar color="primary" size="20" class="ue-form-summary-item__order">
        {{ index + 1 }}
      </v-avatar>
      <span class="ue-form-summary-item__step">
        {{ $t('fields.step') }} {{ index + 1 }}
      </span>
    </div>

    <!-- Secondary title -->
    <div class="ue-form-summary-item__title text-truncate mb-4">
      {{ title }}
    </div>

    <!-- Step body -->
    <div class="d-flex flex-wrap ga-2">
      <template
        v-for="(val, inputName) in model"
        :key="`stepper-preview-item-subtitle-${inputName}`"
      >
        <template
          v-for="(context, key) in model[inputName]"
          :key="`stepper-preview-item-subtitle-${inputName}-${key}`"
        >
          <!-- Array context -->
          <div v-if="Array.isArray(context)" class="ue-form-summary-item__group w-100">
            <div class="ue-form-summary-item__group-title">
              {{ window.__isObject(model[inputName]) ? key : context[0] }}
            </div>
            <div class="ue-form-summary-item__group-value">
              {{ (window.__isObject(model[inputName]) ? context : context.slice(1)).join(' ') }}
            </div>
          </div>

          <!-- Non-array context -->
          <v-chip
            v-else
            size="small"
            variant="flat"
            color="primary-lighten-4"
            class="ue-form-summary-item__chip"
          >
            {{ context }}
          </v-chip>
        </template>
      </template>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FormSummaryItem',
  props: {
    index: {
      type: Number,
      required: true
    },
    title: {
      type: String,
      required: true
    },
    model: {
      type: Object,
      required: true
    }
  },
}
</script>

<style lang="sass">
  .ue-form-summary-item
    background: rgb(var(--v-theme-surface))
    border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity))
    border-radius: 12px
    padding: 20px 24px
    margin-bottom: 16px

    &__order
      font-size: 0.75rem
      font-weight: 600

    &__step
      color: rgb(var(--v-theme-primary))
      font-size: 0.875rem
      font-weight: 600
      text-transform: uppercase

    &__title
      color: rgb(var(--v-theme-primary-darken-3))
      font-size: 0.875rem
      font-weight: 600

    &__chip
      color: rgb(var(--v-theme-primary-darken-3)) !important
      font-weight: 500

    &__group
      & + &
        margin-top: 8px

    &__group-title
      color: rgb(var(--v-theme-primary-darken-3))
      font-size: 0.8125rem
      font-weight: 600

    &__group-value
      color: rgba(var(--v-theme-on-surface), 0.7)
      font-size: 0.8125rem
</style>
