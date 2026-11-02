<template>
  <v-radio-group v-model="input" direction="horizontal"  class="v-input-radio-group" hide-details>
    <ue-title v-if="label" :text="label" color="grey-darken-5" padding="b-4" margin="b-0" transform="none"/>
    <v-row
      :class="[
        $vuetify.display.mdAndUp && isCentered ? 'me-auto ms-auto' : '',
      ]"
      :style="{
        ...(!$vuetify.display.mobile ? {maxWidth: maxWidth, minWidth: minWidth} : {}),
      }">
      <v-col v-for="(item, i) in items" :key="`col-${item[itemValue]}`" :cols="12" :md="6">
        <v-sheet
          v-if="variant === 'card'"
          v-ripple
          :class="[
            'v-input-radio-group__card h-100',
            { 'v-input-radio-group__card--selected': input == item[itemValue] },
          ]"
          :color="input == item[itemValue] ? color : 'surface'"
          role="button"
          @click="($attrs.disabled || protectInitialValue) ? null : input = item[itemValue]"
        >
          <v-radio
            :value="item[itemValue]"
            :color="input == item[itemValue] ? 'white' : color"
            :disabled="$attrs.disabled ?? false"
            density="compact"
            class="flex-grow-0"
          />
          <div>
            <div class="v-input-radio-group__card-title">{{ item[itemTitle].toUpperCase() }}</div>
            <div v-if="item[descriptionTitle]"
              class="v-input-radio-group__card-description"
              v-html="item[descriptionTitle]"
            />
          </div>
        </v-sheet>
        <v-btn
          v-else
          block
          class="v-input-radio-group__btn"
          :color="color"
          :variant="input == item[itemValue] ? 'elevated' : 'outlined'"
          :disabled="$attrs.disabled ?? false"
          :readonly="protectInitialValue"
          @click="input=item[itemValue]"
        >
          <template #prepend>
            <v-radio :value="item[itemValue]" :disabled="$attrs.disabled ?? false"></v-radio>
          </template>
          {{ item[itemTitle].toUpperCase() }}
        </v-btn>

        <div v-if="variant !== 'card' && item[descriptionTitle]" class="text-body-small pa-2" v-html="item[descriptionTitle]"></div>
      </v-col>
    </v-row>
  </v-radio-group>
</template>

<script setup>
import { useInput, makeInputProps, makeInputEmits } from '@/hooks'

const props = defineProps({
  ...makeInputProps(),
  items: {
    type: Object,
    default: () => []
  },
  itemTitle: {
    type: String,
    default: 'name'
  },
  itemValue: {
    type: String,
    default: 'id'
  },
  descriptionTitle: {
    type: String,
    default: 'description'
  },
  color: {
    type: String,
    default: 'primary'
  },
  maxWidth: {
    type: [String, Number],
    default: null
  },
  minWidth: {
    type: [String, Number],
    default: null
  },
  isCentered: {
    type: Boolean,
    default: false
  },
  variant: {
    type: String,
    default: 'button',
    validator: (value) => ['button', 'card'].includes(value)
  }
})

const emit = defineEmits([...makeInputEmits])

const initializeInput = (val) => {
  return val
}

const { input } = useInput(props, {
  ...{
    emit
  },
  ...{ initializeInput }
})

</script>

<style lang="sass">
  .v-input-radio-group
    .v-input__control
      display: block


    .v-input-radio-group__btn
      padding-left: 4px
      justify-content: flex-start !important

      .v-btn__content
        width: 100%
        white-space: normal

    .v-input-radio-group__card
      display: flex
      align-items: flex-start
      gap: 16px
      min-height: 92px
      padding: 24px
      border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity))
      border-radius: 12px
      cursor: pointer

      .v-input-radio-group__card-title
        font-size: 14px
        font-weight: 500
        line-height: 24px
        color: rgb(var(--v-theme-primary))

      .v-input-radio-group__card-description
        margin-top: 4px
        font-size: 12px
        line-height: 18px
        color: rgb(var(--v-theme-primary-darken-3))

      &--selected
        border-color: transparent

        .v-input-radio-group__card-title,
        .v-input-radio-group__card-description
          color: rgb(var(--v-theme-on-primary))


</style>

<style lang="scss">

</style>
