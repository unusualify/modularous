<!--
  validateOn: omit prop to match other fields (Vuetify default = input → validate while typing).
  Set schema.validateOn to "blur" or "blur lazy" if you only want API check on focus out.
  Uniqueness API is debounced ~300ms when input-driven validation is active.

  Translated fields: Locale.vue receives :obj from FormBase and forwards it to children (same as non-translated).
  effectiveSchema still falls back to flat props when obj.schema is missing (legacy callers).

  When manageActive is true (default from SlugHydrate), modelValue is `{ slug: string, active: boolean }` per locale.
  Legacy string values are normalized for display and upgraded on edit.

  When SlugHydrate sets `parentSegmentPrefixByLocale` (CMS parent-segment bindings for HasParentSegment / repository
  ParentSegmentTrait), the field shows that path as Vuetify `prefix` for the active locale.

  When schema provides {@code slugSourceValue} (e.g. from title via {@code formatSet}) and {@code generateEndpoint},
  the Published switch stays in {@code #append}; the source chip plus an icon-only generate control (tooltip: {@code Generate Slug}) use {@code #append-inner}.
  POST {@code inputs.slug.generate} proposes a unique slug (CMS: slug table + public URL registry); stale pulse when the source text changes after the last generate.
-->
<template>
  <v-text-field
    ref="VInput"
    v-model="slugText"
    :loading="validating"
    :rules="mergedRules"
    :prefix="slugFieldPrefix"
    :hint="publicPathPreviewLine || undefined"
    :persistent-hint="Boolean(publicPathPreviewLine)"
    v-bind="$lodash.omit(textFieldBind, ['class'])"
    :class="['ue-input-slug', textFieldBind.class]"
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
    <template v-if="showSlugGenerateToolbar" #append-inner>
      <div class="ue-input-slug__generate-stack d-inline-flex align-center flex-shrink-0 ml-1" style="gap: 8px">
        <v-chip
          size="small"
          variant="outlined"
          density="compact"
          :color="sourceStale ? 'warning' : 'success'"
          class="text-truncate"
          :class="{ 'slug-source-pulse': sourceStale }"
          style="max-width: min(100vw, 220px)"
        >
          <v-icon
            start
            size="16"
            :color="sourceStale ? 'warning' : 'success'"
            :icon="sourceStale ? 'mdi-record-circle' : 'mdi-check-circle'"
          />
          <span class="text-truncate">{{ slugSourceDisplay }}</span>
        </v-chip>
        <v-tooltip location="top">
          <template #activator="{ props: genTipProps }">
            <v-btn
              v-bind="genTipProps"
              icon
              size="x-small"
              density="compact"
              variant="tonal"
              color="primary"
              :loading="generating"
              :disabled="!slugSourceDisplay.trim()"
              :aria-label="generateSlugTooltip"
              @click="generateSlugFromSource"
            >
              <v-icon icon="mdi-shimmer" size="20" />
            </v-btn>
          </template>
          <span>{{ generateSlugTooltip }}</span>
        </v-tooltip>
      </div>
    </template>
  </v-text-field>
</template>

<script setup>
  import { ref, computed, inject, unref, watch } from 'vue'
  import axios from 'axios'
  import { useI18n } from 'vue-i18n'
  import { omit } from 'lodash-es'
  import { makeInputProps, makeInputEmits, useValidation, useStepUpAwareJsonPost } from '@/hooks'

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
    /**
     * Title (or other source) text for optional one-click slug generation; often set via form {@code formatSet} from the title field.
     */
    slugSourceValue: {
      type: String,
      default: '',
    },
    /** POST {@see inputs.slug.generate}; omitted when set in schema by {@see SlugHydrate}. */
    generateEndpoint: {
      type: String,
      default: '',
    },
  })

  const { t, locale: i18nLocale } = useI18n({ useScope: 'global' })
  const { postJson } = useStepUpAwareJsonPost()
  const generateSlugTooltip = computed(() =>
    t('fields.slug_generate_from_source', 'Generate Slug'),
  )
  const VInput = ref(null)
  const { generateInputRules } = useValidation(props)
  const validating = ref(false)
  const generating = ref(false)
  /** Source snapshot after the last successful “Generate slug” (stale indicator vs {@link slugSourceDisplay}). */
  const lastGeneratedSource = ref(null)

  /** `undefined` = not loaded yet; `null` = no max (unlimited); number = max path segments (see `cms_routing.admin.slug_max_path_segments`). */
  const slugMaxPathSegments = ref(undefined)
  let routingMetaLoadGen = 0

  /** Sync rule aligned with {@see \Modules\Cms\Services\CmsSlugInputValidationService::slugPathSegmentPolicyFailure}. */
  function slugSegmentCountRule(value) {
    const max = slugMaxPathSegments.value
    if (max === undefined || max === null || Number(max) < 1) {
      return true
    }
    const trimmed = String(value ?? '').trim()
    if (!trimmed) {
      return true
    }
    const norm = trimmed.replace(/\\/g, '/')
    const segments = norm
      .replace(/^\/+|\/+$/g, '')
      .split('/')
      .filter(Boolean)
    if (segments.length > Number(max)) {
      return t('validation.slug_max_segments', 'The slug may contain at most {max} URL segment(s).', {
        max: Number(max),
      })
    }

    return true
  }

  const mergeFormFieldErrors = inject('mergeFormFieldErrors', null)
  const clearFormFieldErrorKey = inject('clearFormFieldErrorKey', null)

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

  const slugSourceDisplay = computed(() => {
    const raw = props.slugSourceValue ?? effectiveSchema.value.slugSourceValue
    if (raw === undefined || raw === null) {
      return ''
    }

    return String(raw)
  })

  const generateEndpointResolved = computed(() => {
    const raw = effectiveSchema.value.generateEndpoint ?? props.generateEndpoint ?? ''
    if (raw) {
      return raw
    }

    return deriveSlugGenerateUrlFromValidateEndpoint(endpoint.value)
  })

  function deriveSlugGenerateUrlFromValidateEndpoint(slugEndpoint) {
    if (!slugEndpoint || typeof slugEndpoint !== 'string') {
      return ''
    }

    return slugEndpoint.replace(/\/inputs\/slug\/validate\/?$/i, '/inputs/slug/generate')
  }

  const sourceStale = computed(() => {
    const s = slugSourceDisplay.value.trim()
    if (!s) {
      return false
    }

    return lastGeneratedSource.value !== s
  })

  const showSlugGenerateToolbar = computed(
    () =>
      Boolean(generateEndpointResolved.value)
      && Boolean(moduleName.value)
      && Boolean(routeName.value)
      && slugSourceDisplay.value.trim() !== '',
  )

  function applyGeneratedSlug(nextText) {
    const s = String(nextText ?? '')
    if (!manageActive.value) {
      emit('update:modelValue', s)

      return
    }
    emitModel({ slug: s, active: payload.value.active })
  }

  async function generateSlugFromSource() {
    const src = slugSourceDisplay.value.trim()
    if (!src) {
      return
    }
    const url = generateEndpointResolved.value
    if (!url || !moduleName.value || !routeName.value) {
      return
    }

    generating.value = true
    try {
      const { data } = await postJson(url, {
        module: moduleName.value,
        route: routeName.value,
        source: src,
        locale: localeComputed.value,
        locale_scoped: localeScopedVal.value,
        exclude_id: excludeIdComputed.value,
      })
      const next = data?.slug ?? data?.normalized
      if (next !== undefined && next !== null) {
        applyGeneratedSlug(String(next))
        lastGeneratedSource.value = src
      }
    } catch {
      // Optional: surface via form errors; keep UX quiet on failure
    } finally {
      generating.value = false
    }
  }

  /**
   * Derives `GET …/cms/routing-meta` from the slug validate endpoint URL (same API prefix).
   */
  function routingMetaUrlFromSlugEndpoint(slugEndpoint) {
    if (!slugEndpoint || typeof slugEndpoint !== 'string') {
      return null
    }
    try {
      const base = typeof window !== 'undefined' ? window.location.origin : 'http://localhost'
      const u = new URL(slugEndpoint, base)
      const m = u.pathname.match(/^(.*\/)inputs\/slug\/validate\/?$/i)
      if (!m) {
        return null
      }

      return `${u.origin}${m[1]}cms/routing-meta`
    } catch {
      return null
    }
  }

  async function loadCmsRoutingMeta() {
    const gen = ++routingMetaLoadGen
    slugMaxPathSegments.value = undefined
    const url = routingMetaUrlFromSlugEndpoint(endpoint.value)
    if (!url) {
      if (gen === routingMetaLoadGen) {
        slugMaxPathSegments.value = null
      }

      return
    }
    try {
      const { data } = await axios.get(url)
      if (gen !== routingMetaLoadGen) {
        return
      }
      const max = data?.admin?.slug_max_path_segments
      slugMaxPathSegments.value = max == null || max === '' ? null : Number(max)
    } catch {
      if (gen !== routingMetaLoadGen) {
        return
      }
      slugMaxPathSegments.value = null
    }
  }

  watch(
    () => endpoint.value,
    () => {
      void loadCmsRoutingMeta()
    },
    { immediate: true },
  )

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

  /**
   * Locale → normalized path from CMS parent-segment resolver (`*` = all locales).
   */
  const parentSegmentPrefixByLocale = computed(
    () => effectiveSchema.value.parentSegmentPrefixByLocale ?? null,
  )

  /**
   * Vuetify field prefix: parent path for this locale (e.g. `blog/corp/`), when CMS bindings exist.
   */
  const slugFieldPrefix = computed(() => {
    const map = parentSegmentPrefixByLocale.value
    if (!map || typeof map !== 'object') {
      return undefined
    }
    const loc = String(localeComputed.value ?? '')
    const raw = map[loc] ?? map['*'] ?? ''
    if (raw === undefined || raw === null || String(raw).trim() === '') {
      return undefined
    }
    const inner = String(raw).replace(/^\/+|\/+$/g, '')

    return inner ? `${inner}/` : undefined
  })

  /** Shown under the field when {@see SlugHydrate} sends `cmsPublicPathPreview` (CMS public URL segments). */
  const cmsPublicPathPreview = computed(() => effectiveSchema.value.cmsPublicPathPreview ?? null)

  const publicPathPreviewLine = computed(() => {
    const meta = cmsPublicPathPreview.value
    if (!meta || meta.prefix === undefined || meta.prefix === null) {
      return ''
    }
    const slug = manageActive.value ? payload.value.slug : String(props.modelValue ?? '')
    const trimmed = (slug ?? '').trim()
    if (!trimmed) {
      return ''
    }
    const prefix = String(meta.prefix).replace(/^\/+|\/+$/g, '')
    const loc = String(localeComputed.value ?? '')
    const def = String(meta.default_locale ?? 'en')
    const hide = Boolean(meta.hide_default_locale)
    const segments = [prefix]
    if (!hide || loc !== def) {
      segments.push(loc)
    }
    const pathPart = trimmed.replace(/^\/+/, '')
    segments.push(...pathPart.split('/').filter(Boolean))

    return '/' + segments.filter(Boolean).join('/').replace(/\/+/g, '/')
  })

  /** Laravel / useForm.setSchemaErrors dotted key for this input (translated: `slug_segment.en`). */
  const formErrorDotKey = computed(() => {
    const fieldName = effectiveSchema.value?.name ?? props.obj?.schema?.name
    if (!fieldName) {
      return null
    }

    return `${fieldName}.${localeComputed.value}`
  })

  function applySlugValidateWarnings(data) {
    const key = formErrorDotKey.value
    if (!key) {
      return
    }
    const merge = mergeFormFieldErrors ? unref(mergeFormFieldErrors) : null
    const clear = clearFormFieldErrorKey ? unref(clearFormFieldErrorKey) : null
    if (Array.isArray(data?.warnings) && data.warnings.length > 0) {
      merge?.({ [key]: data.warnings.map(String) })
    } else if (clear) {
      clear(key)
    }
  }

  function clearSlugValidateWarnings() {
    const key = formErrorDotKey.value
    if (!key) {
      return
    }
    const clear = clearFormFieldErrorKey ? unref(clearFormFieldErrorKey) : null
    clear?.(key)
  }

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
      'generateEndpoint',
      'translated',
      'localeScoped',
      'excludeId',
      'locale',
      'manageActive',
      'cmsPublicPathPreview',
      'parentSegmentPrefixByLocale',
      'slugSourceValue',
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
          clearSlugValidateWarnings()
          resolve(true)

          return
        }

        if (!endpoint.value || !moduleName.value || !routeName.value) {
          clearSlugValidateWarnings()
          resolve(true)

          return
        }

        const seq = ++requestSeq

        validating.value = true
        clearSlugValidateWarnings()

        try {
          const { data } = await postJson(endpoint.value, {
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

          applySlugValidateWarnings(data)

          resolve(data.valid ? true : (data.message || t('validation.slug_invalid', 'Invalid slug')))
        } catch (e) {
          clearSlugValidateWarnings()
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

    return [...base, slugSegmentCountRule, slugAsyncValidator].filter((rule) => typeof rule === 'function')
  })
</script>

<style scoped>
  .ue-input-slug .slug-source-pulse {
    animation: slug-source-glow 1.35s ease-in-out infinite;
  }
  @keyframes slug-source-glow {
    0%,
    100% {
      opacity: 1;
    }
    50% {
      opacity: 0.55;
    }
  }
</style>
