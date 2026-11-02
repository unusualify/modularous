<template>
  <v-input
    v-model="input[value]"
    hideDetails="auto"
    :variant="boundProps.variant"
    class="v-input-checkbox-card"
    :readonly="readonly"
  >
    <template v-slot:default="defaultSlot">
      <v-card
        :class="[
          'v-input-checkbox-card__item d-flex flex-column',
          { 'v-input-checkbox-card__item--selected': isSelected },
          disabled ? 'v-input-checkbox-card__item--disabled' : ''
        ]"
        variant="flat"
        role="checkbox"
        :aria-checked="isSelected"
        @click="toggleSelection"
        :disabled="disabled"
      >
        <div class="d-flex align-start justify-space-between ga-2 pa-4 pb-2">
          <div
            :class="[
              'v-input-checkbox-card__title',
              isSelected && activeTitleColor ? `text-${activeTitleColor}` : '',
            ]"
          >
            {{ title }}
          </div>
          <v-chip v-if="isSelected"
            :color="checkboxColor"
            variant="flat"
            size="x-small"
            prepend-icon="mdi-check"
            class="flex-shrink-0"
          >
            {{ $t('Selected') }}
          </v-chip>
        </div>

        <div v-if="description" class="v-input-checkbox-card__description px-4">
          {{ description }}
        </div>

        <v-spacer />

        <div v-if="stats" class="d-flex px-4 py-5">
          <template v-for="(stat, index) in stats" :key="index">
            <v-divider v-if="index > 0" vertical class="my-1" />
            <div class="flex-1-1 text-center">
              <div class="v-input-checkbox-card__stat-value" :class="`text-${stat.color || 'primary'}`">
                {{ formatStat(stat.value) }}
              </div>
              <div class="v-input-checkbox-card__stat-label">{{ stat.label }}</div>
            </div>
          </template>
        </div>
      </v-card>
    </template>
  </v-input>
</template>

<script>
  import { useInput, makeInputProps, makeInputEmits } from '@/hooks'

  export default {
    name: 'v-input-checkbox-card',
    emits: [...makeInputEmits],
    props: {
      ...makeInputProps(),
      title: {
        type: String,
        required: true
      },
      description: {
        type: String,
        default: ''
      },
      disabled: {
        type: Boolean,
        default: false
      },
      readonly: {
        type: Boolean,
        default: false
      },
      value: {
        type: [Number, String],
        default: null
      },
      activeColor: {
        type: String,
        default: null
      },
      activeTitleColor: {
        type: String,
        default: null
      },
      checkboxColor: {
        type: String,
        default: 'primary'
      },
      stats: {
        type: Array,
        default: null,
        validator: (value) => {
          return value === null || value.every(stat =>
            'label' in stat &&
            'value' in stat &&
            (!('color' in stat) || typeof stat.color === 'string')
          )
        }
      },
      checkboxOnRight: {
        type: Boolean,
        default: false
      }
    },
    setup(props, context) {
      return {
        ...useInput(props, context)
      }
    },
    data() {
      return {
        input_: false
      }
    },
    computed: {
      isSelected () {
        return Array.isArray(this.input) && this.input.includes(this.value)
      }
    },
    methods: {
      formatStat (value) {
        return Number.isInteger(value) ? String(value).padStart(2, '0') : value
      },
      toggleSelection() {
        if (!this.disabled && !this.readonly) {
          if(this.input.includes(this.value)) {
            this.input = this.input.filter(item => item !== this.value)
          } else {
            this.input = [...this.input, this.value]
          }
        }
      }
    },
  }
</script>

<style lang="sass">
  .v-input-checkbox-card
    &__item
      width: 100%
      cursor: pointer
      border: 1px solid rgb(var(--v-theme-grey-lighten-3))
      border-radius: 12px !important
      transition: background-color .15s, border-color .15s

      &--selected
        border-color: rgb(var(--v-theme-primary))
        background-color: rgba(var(--v-theme-primary), .08) !important

      &--disabled
        opacity: 0.6
        cursor: not-allowed

    &__title
      font-size: 14px
      font-weight: 500
      line-height: 24px
      text-transform: uppercase
      color: rgb(var(--v-theme-primary-darken-3))

    &__description
      font-size: 12px
      line-height: 18px
      color: rgb(var(--v-theme-primary-darken-3))

    &__stat-value
      font-size: 24px
      font-weight: 500
      line-height: 32px

    &__stat-label
      font-size: 12px
      color: rgb(var(--v-theme-primary-darken-3))
</style>
