<template>
  <v-stepper-header class="ue-stepper-header">
    <template v-for="(step, i) in steps" :key="`stepper-item-${i}`">
      <v-divider v-if="i > 0"
        :class="['ue-stepper-header__line', { 'ue-stepper-header__line--complete': activeStep > i }]"
      />
      <v-stepper-item
        :complete="activeStep > i + 1"
        :value="i + 1"
        editable
        color="secondary"
        :class="[
          'ue-stepper-header__item',
          {
            'ue-stepper-header__item--active': activeStep === i + 1,
            'ue-stepper-header__item--complete': activeStep > i + 1,
          }
        ]"
        @click="$emit('step-click', i + 1)"
      >
        <template #icon="iconScope">
          <v-icon v-if="iconScope.hasCompleted" icon="mdi-check" size="small" />
          <template v-else>{{ i + 1 }}</template>
        </template>
        <template #title>
          <span class="ue-stepper-header__title">{{ step }}</span>
        </template>
      </v-stepper-item>
    </template>
  </v-stepper-header>
</template>

<script>
export default {
  name: 'StepperHeader',
  props: {
    forms: {
      type: Array,
      required: true
    },
    activeStep: {
      type: Number,
      required: true
    },
    summaryTitle: {
      type: String,
      default: null
    }
  },
  emits: ['step-click'],
  computed: {
    steps () {
      return [
        ...this.forms.map(form => form.stepTitle ?? form.title),
        this.summaryTitle ?? this.$t('Preview & Summary'),
      ]
    }
  }
}
</script>

<style lang="sass">
.ue-stepper-header.v-stepper-header
  min-height: 68px
  padding: 0 8px
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity))
  border-radius: 12px
  box-shadow: none
  flex-wrap: nowrap

  .ue-stepper-header__item
    flex: 0 0 auto
    padding: 12px 16px
    cursor: pointer
    opacity: 1

    .v-stepper-item__avatar.v-avatar
      width: 28px !important
      height: 28px !important
      margin-inline-end: 8px
      border-radius: 50%
      font-size: 14px
      background: rgb(var(--v-theme-grey-lighten-3))
      color: rgb(var(--v-theme-primary-darken-3))

    .ue-stepper-header__title
      font-size: 14px
      color: rgb(var(--v-theme-primary-darken-3))
      white-space: nowrap

    &--active, &--complete
      .v-stepper-item__avatar.v-avatar
        background: rgb(var(--v-theme-secondary))
        color: rgb(var(--v-theme-on-secondary))

      .ue-stepper-header__title
        color: rgb(var(--v-theme-secondary))
        font-weight: 500

  .ue-stepper-header__line
    margin: 0 8px
    opacity: 1
    border-color: rgb(var(--v-theme-grey-lighten-3))

    &--complete
      border-color: rgb(var(--v-theme-secondary))
</style>
