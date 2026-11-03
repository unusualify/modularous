<template>
  <v-sheet
    class="d-flex flex-column"
    :class="[isLastStep ? 'ue-stepper-summary--final pa-6 bg-primary-darken-1' : 'fill-height ue-stepper-summary--cards bg-transparent']"
  >
    <template v-if="!isLastStep">
      <slot name="summary.forms">
        <v-sheet class="ue-stepper-form__preview fill-height bg-transparent" :border="false">
          <!-- Form summaries -->
          <template v-for="(form, i) in forms" :key="`stepper-summary-item-${i}`">
            <slot
              :name="`summary-form-${i+1}`"
              v-bind="{
                index: i,
                order: i+1,
                title: previewTitles[i],
                isPreviewModelFilled,
                model: models[i],
                schema: schemas[i],
                previewModel: previewModel[i] ?? {},
                length: forms.length
              }"
            >
              <!-- Default summary content -->
              <ue-form-summary-item
                v-if="isPreviewModelFilled(i)"
                :index="i"
                :title="previewTitles[i]"
                :model="previewModel[i]"
              />
            </slot>
          </template>
        </v-sheet>
      </slot>
    </template>
    <template v-else>
      <slot
        name="summary.final"
        v-bind="{
          model: models,
          schema: schemas,
          previewModel: previewModel,
          onComplete: () => $emit('complete-form')
        }"
      ></slot>
    </template>
  </v-sheet>
</template>

<script>
export default {
  name: 'StepperSummary',
  props: {
    isLastStep: Boolean,
    forms: Array,
    activeStep: Number,
    models: Array,
    schemas: Array,
    previewModel: Array,
    previewTitles: Array,
    isPreviewModelFilled: Function
  },
  emits: ['next-form', 'complete-form']
}
</script>

<style lang="sass">
  .ue-stepper-summary--cards
    border: 0 !important

  .ue-stepper-summary--final
    border: 0 !important
    border-radius: 12px !important
</style>
