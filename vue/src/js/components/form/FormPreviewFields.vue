<template>
  <v-row class="ue-form-preview" density="comfortable">
    <template v-for="input in fields" :key="input.name">
      <!-- Boolean: full-width row, label left + read-only switch right -->
      <v-col
        v-if="display(input).kind === 'boolean'"
        cols="12"
        class="ue-form-preview__col ue-form-preview__col--boolean d-flex align-center justify-space-between ga-4 py-3"
      >
        <div class="ue-form-preview__label text-body-large font-weight-medium text-break">
          {{ input.label }}
        </div>
        <v-switch
          :model-value="display(input).value"
          :aria-label="input.label"
          class="ue-form-preview__switch flex-grow-0"
          color="primary"
          density="compact"
          inset
          readonly
          hide-details
        />
      </v-col>
      <v-col
        v-else
        v-bind="colAttrs(input)"
        :class="['ue-form-preview__col py-3', { 'ue-form-preview__col--muted': isMuted(input) }]"
      >
        <div class="ue-form-preview__label text-body-small text-medium-emphasis mb-1 text-break">
          {{ input.label }}
        </div>
        <div class="ue-form-preview__value text-body-large font-weight-medium text-break">
          <span v-if="display(input).kind === 'empty'" class="text-medium-emphasis">—</span>
          <span v-else>{{ display(input).value }}</span>
        </div>
      </v-col>
    </template>
  </v-row>
</template>

<script>
import { computed } from 'vue'
import { getFormPreviewFields, getFormPreviewValue } from '@/utils/formPreview'
import { resolveFormEventTokens } from '@/utils/resolveFormEventTokens'
import formatHelpers from '@/utils/formEventFormatters/helpers'

export default {
  name: 'ue-form-preview-fields',
  props: {
    schema: {
      type: Object,
      default: () => ({}),
    },
    model: {
      type: Object,
      default: () => ({}),
    },
  },
  setup (props) {
    const fields = computed(() => getFormPreviewFields(props.schema))

    /**
     * Grid attrs for a preview column. `previewCol` (optional, per input) overrides
     * `col` only in preview mode, so the edit layout stays untouched.
     */
    const colAttrs = (input) => {
      const col = input.previewCol ?? input.col
      if (!col) {
        return { cols: 12 }
      }
      if (typeof col === 'object') {
        const { class: _class, ...attrs } = col
        return Object.keys(attrs).length ? attrs : { cols: 12 }
      }
      return { cols: col }
    }

    const display = (input) => getFormPreviewValue(input, props.model)

    /**
     * Names of inputs that a `set:<target>:disabled:<format>` form event would disable
     * for the current model (e.g. "I don't have a company" → name/phone/email).
     * Evaluated from the saved model because form events only run on input change.
     */
    const mutedNames = computed(() => {
      const names = new Set()

      Object.values(props.schema ?? {}).forEach((source) => {
        if (!source || !source.name) {
          return
        }

        let value = props.model?.[source.name]
        if (typeof value === 'boolean') {
          value = Number(value)
        }
        if (!value) {
          return
        }

        resolveFormEventTokens(source).forEach((token) => {
          const [method, target, prop, format] = token.split(':')
          if (!['set', 'formatSet'].includes(method) || prop !== 'disabled' || !target || !format) {
            return
          }
          if (formatHelpers.getNewValue(format, value, source)) {
            names.add(target)
          }
        })
      })

      return names
    })

    const isMuted = (input) => mutedNames.value.has(input.name)

    return {
      fields,
      colAttrs,
      display,
      isMuted,
    }
  },
}
</script>

<style lang="scss" scoped>
// Fields disabled by a form event (e.g. company-only fields for personal accounts)
.ue-form-preview__col--muted {
  opacity: 0.3;
}

// Read-only (preview) switch: soft tinted pill with outlined border when off
.ue-form-preview__switch {
  // read-only display: no hand cursor
  &,
  :deep(*) {
    cursor: default !important;
  }

  :deep(.v-selection-control:not(.v-selection-control--dirty)) {
    .v-switch__track {
      background-color: #E0F2F3;
      border: 1px solid #6E8A94;
      opacity: 1;
    }

    .v-switch__thumb {
      background-color: #78909C;
      color: #78909C;
      box-shadow: none;
    }
  }
}
</style>
