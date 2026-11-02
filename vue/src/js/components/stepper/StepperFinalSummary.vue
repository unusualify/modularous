<template>
  <div class="ue-stepper-final-summary">
    <div class="ue-stepper-final-summary__title">{{ $t('Your Order').toUpperCase() }}</div>

    <!-- Summary sections -->
    <slot name="body">

    </slot>
  </div>

  <v-divider class="ue-stepper-final-summary__divider my-4" />

  <!-- Total -->
  <div class="d-flex align-center justify-space-between ga-4 text-white">
    <div class="ue-stepper-final-summary__total-label">
      <slot name="total.label">
        {{ $t('Total').toUpperCase() }}
      </slot>
    </div>
    <div class="ue-stepper-final-summary__total">
      <slot name="total">
      </slot>
    </div>
  </div>

  <!-- Description -->
  <div class="ue-stepper-final-summary__description mt-4 mb-8">
    <slot name="description">
    </slot>
  </div>

  <!-- Complete button -->
  <v-btn
    class="v-stepper-form__nextButton ue-stepper-final-summary__button"
    block
    size="x-large"
    rounded="pill"
    variant="flat"
    color="white"
    :disabled="loading || isCompleted"
    :loading="loading"
    @click="$emit('complete')"
  >
    {{ $t('Complete Request').toUpperCase() }}
  </v-btn>
</template>

<script>
export default {
  name: 'StepperFinalSummary',
  props: {
    loading: {
      type: Boolean,
      default: false
    },
    isCompleted: {
      type: Boolean,
      default: false
    }
  },
  emits: ['complete']
}
</script>

<style lang="sass">
  .ue-stepper-final-summary
    color: rgb(var(--v-theme-on-primary))

    &__title
      font-size: 0.9375rem
      font-weight: 500
      letter-spacing: 0.02em
      margin-bottom: 24px

    &__divider
      border-color: rgba(var(--v-theme-on-primary), 0.5) !important
      opacity: 1 !important

    &__total-label
      font-size: 1.25rem
      font-weight: 500

    &__total
      font-size: 1.75rem
      font-weight: 500

    &__description
      font-size: 0.75rem
      line-height: 1.5
      color: rgba(var(--v-theme-on-primary), 0.85)

    &__button
      color: rgb(var(--v-theme-primary)) !important
      font-weight: 600
      letter-spacing: 0.02em
</style>
