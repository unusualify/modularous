<!--
  validateOn: omit prop to match other fields (Vuetify default = input → validate while typing).
  Set schema.validateOn to "blur" or "blur lazy" if you only want API check on focus out.
  Uniqueness API is debounced ~300ms when input-driven validation is active.

  Translated fields: Locale.vue receives :obj from FormBase and forwards it to children (same as non-translated).
  effectiveSchema still falls back to flat props when obj.schema is missing (legacy callers).

  When manageActive is true (default from SlugHydrate), modelValue is `{ slug: string, active: boolean }` per locale.
  Legacy string values are normalized for display and upgraded on edit.
-->
<template>
  <v-text-field
    ref="VInput"
    v-model="slugText"
    :loading="validating"
    :rules="mergedRules"
    v-bind="textFieldBind"
  >
    <template v-if="manageActive" #append>
      <v-tooltip location="top">
        <template #activator="{ props: tipProps }">
          <span v-bind="tipProps" class="d-inline-flex align-center">
            <v-switch
              v-model="slugActive"
              class="ma-0"
              color="primary"
              density="compact"
              hide-details
              inset
              :ripple="false"
              :aria-label="t('fields.slug_active', 'Published')"
            />
          </span>
        </template>
        <span>{{ t('fields.slug_active', 'Published') }}</span>
      </v-tooltip>
    </template>
  </v-text-field>
</template>

<script setup>
  import { ref, computed } from 'vue'
  import axios from 'axios'
  import { useI18n } from 'vue-i18n'
  import { omit } from 'lodash-es'
  import { makeInputProps, makeInputEmits, useValidation } from '@/hooks'

  defineOptions({
    name: 'v-input-slug',
  })

  const emit = defineEmits([...makeInputEmits])

  const props = defineProps({
    ...makeInputProps(),
    endpoint: {
      type: String,
      default: '',
    },
    localeKey: {
      type: String,
      default: null,
    },
    excludeId: {
      type: [Number, String],
      default: null,
    },
    locale: {
      type: String,
      default: null,
    },
    /** From hydrate / v-input-locale spread (declare so they are not dropped as attrs). */
    _moduleName: {
      type: String,
      default: undefined,
    },
    _routeName: {
      type: String,
      default: undefined,
    },
    /**
     * Optional. Omit = same as other inputs (Vuetify default validateOn = input).
     */
    validateOn: {
      type: String,
      default: undefined,
    },
    /** Locale / FormBase may pass Laravel-style rules alongside obj.schema */
    rules: {
      type: [String, Array],
      default: undefined,
    },
    /** From Locale (attributes.required); used if schema has no rules string */
    required: {
      type: Boolean,
      default: undefined,
    },
  })

  const { t, locale: i18nLocale } = useI18n({ useScope: 'global' })
  const VInput = ref(null)
  const { generateInputRules } = useValidation(props)
  const validating = ref(false)

  /** FormBaseField uses obj.schema; v-input-locale spreads field config as top-level props. */
  const effectiveSchema = computed(() => {
    const nested = props.obj?.schema
    if (nested && typeof nested === 'object' && Object.keys(nested).length > 0) {
      return nested
    }

    return omit(props, [
      'modelValue',
      'obj',
      'hideIfEmpty',
      'default',
      'protectInitialValue',
      'isEditing',
      'editable',
      'creatable',
      'rules',
      'required',
    ])
  })

  const manageActive = computed(() => {
    const s = effectiveSchema.value
    if (s.manageActive === false) {
      return false
    }
    if (s.translated === true) {
      return true
    }

    return s.manageActive === true
  })

  function normalizePayload(raw) {
    if (!manageActive.value) {
      return { slug: String(raw ?? ''), active: true }
    }
    if (typeof raw === 'string') {
      return { slug: raw, active: true }
    }
    if (raw && typeof raw === 'object' && Object.prototype.hasOwnProperty.call(raw, 'slug')) {
      return {
        slug: String(raw.slug ?? ''),
        active: raw.active !== false,
      }
    }

    return { slug: '', active: true }
  }

  const payload = computed(() => normalizePayload(props.modelValue))

  function emitModel(next) {
    if (!manageActive.value) {
      emit('update:modelValue', next.slug)

      return
    }
    emit('update:modelValue', { slug: next.slug, active: next.active })
  }

  const slugText = computed({
    get: () => (manageActive.value ? payload.value.slug : String(props.modelValue ?? '')),
    set: (v) => {
      const s = v ?? ''
      if (!manageActive.value) {
        emit('update:modelValue', s)

        return
      }
      emitModel({ slug: s, active: payload.value.active })
    },
  })

  const slugActive = computed({
    get: () => payload.value.active,
    set: (v) => {
      if (!manageActive.value) {
        return
      }
      emitModel({ slug: payload.value.slug, active: !!v })
    },
  })

  const moduleName = computed(() => effectiveSchema.value._moduleName ?? props._moduleName)
  const routeName = computed(() => effectiveSchema.value._routeName ?? props._routeName)
  const endpoint = computed(() => effectiveSchema.value.endpoint ?? props.endpoint)
  const localeScopedVal = computed(() => {
    const v = effectiveSchema.value.localeScoped
    if (v === false) {
      return false
    }

    return true
  })

  const excludeIdComputed = computed(() => {
    const raw = effectiveSchema.value.excludeId ?? props.excludeId
    if (raw === null || raw === undefined || raw === '') {
      return null
    }
    const n = Number(raw)

    return Number.isNaN(n) ? null : n
  })

  const localeComputed = computed(() =>
    props.localeKey ?? effectiveSchema.value.locale ?? i18nLocale.value,
  )

  /** Undefined → Vuetify default ("input"), same as other form fields. */
  const validateOnResolved = computed(() => effectiveSchema.value.validateOn ?? props.validateOn)

  const validateOnBind = computed(() => {
    const v = validateOnResolved.value
    if (v === undefined || v === null || v === '') {
      return {}
    }

    return { validateOn: v }
  })

  /** Debounce API when rules run on input (Vuetify default or explicit input). */
  const debounceMs = computed(() => {
    const raw = validateOnResolved.value
    const v = raw === undefined || raw === null || raw === '' ? 'input' : String(raw)

    return /\binput\b/.test(v) ? 300 : 0
  })

  const fieldBind = computed(() =>
    omit(effectiveSchema.value ?? {}, [
      'rules',
      'type',
      'validateOn',
      '_moduleName',
      '_routeName',
      'endpoint',
      'translated',
      'localeScoped',
      'excludeId',
      'locale',
      'manageActive',
    ]),
  )

  const textFieldBind = computed(() => ({
    ...fieldBind.value,
    ...validateOnBind.value,
  }))

  let debounceTimer = null
  let requestSeq = 0

  function slugAsyncValidator(value) {
    clearTimeout(debounceTimer)

    return new Promise((resolve) => {
      const run = async () => {
        const trimmed = (value ?? '').trim()

        // Empty: let sync rules (e.g. requiredRule from generateInputRules) handle — Vuetify needs function rules, not raw strings
        if (!trimmed) {
          resolve(true)

          return
        }

        if (!endpoint.value || !moduleName.value || !routeName.value) {
          resolve(true)

          return
        }

        const seq = ++requestSeq

        validating.value = true

        try {
          const { data } = await axios.post(endpoint.value, {
            module: moduleName.value,
            route: routeName.value,
            value: trimmed,
            locale: localeComputed.value,
            locale_scoped: localeScopedVal.value,
            exclude_id: excludeIdComputed.value,
          })

          if (seq !== requestSeq) {
            resolve(true)

            return
          }

          resolve(data.valid ? true : (data.message || t('validation.slug_invalid', 'Invalid slug')))
        } catch (e) {
          resolve(t('validation.slug_network', 'Could not validate slug'))
        } finally {
          validating.value = false
        }
      }

      const ms = debounceMs.value
      if (ms > 0) {
        debounceTimer = setTimeout(run, ms)
      } else {
        debounceTimer = setTimeout(run, 0)
      }
    })
  }

  /**
   * Laravel-style string rules (e.g. "required|max:255") → Vuetify function rules via generateInputRules
   * (same as useValidation / Filepond). Raw strings are not valid Vuetify rule entries.
   */
  const mergedRules = computed(() => {
    const schema = effectiveSchema.value
    let rulesRaw = schema?.rawRules ?? schema?.rules

    if (
      (rulesRaw === undefined || rulesRaw === null)
      && props.rules !== undefined
      && props.rules !== null
    ) {
      if (Array.isArray(props.rules) && props.rules.length > 0) {
        rulesRaw = props.rules
      } else if (typeof props.rules === 'string' && props.rules !== '') {
        rulesRaw = props.rules
      }
    }

    let base = []

    if (rulesRaw === undefined || rulesRaw === null) {
      base = []
    } else if (typeof rulesRaw === 'string') {
      base = generateInputRules({ rules: rulesRaw })
    } else if (Array.isArray(rulesRaw)) {
      base = rulesRaw.flatMap((rule) => {
        if (typeof rule === 'function') {
          return [rule]
        }
        if (typeof rule === 'string') {
          return generateInputRules({ rules: rule })
        }

        return []
      })
    }

    if (
      base.length === 0
      && (props.required === true || effectiveSchema.value?.required === true)
    ) {
      base = generateInputRules({ rules: 'required' })
    }

    return [...base, slugAsyncValidator].filter((rule) => typeof rule === 'function')
  })
</script>
