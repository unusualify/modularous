/**
 * useLocale - Composable replacement for LocaleMixin.
 * Provides locale state and helpers from Vuex language store.
 */
import { computed, ref } from 'vue'
import { useStore } from 'vuex'
import { LANGUAGE } from '@/store/mutations'

const RTL_LOCALES = ['ar', 'arc', 'dv', 'fa', 'ha', 'he', 'khw', 'ks', 'ku', 'ps', 'ur', 'yi']

export default function useLocale (props = {}) {
  const store = useStore()
  const _locale = ref(props.locale ?? null)

  const currentLocale = computed(() => store.state.language?.active ?? null)
  const languages = computed(() => store.state.language?.all ?? [])

  const hasLocale = computed(() => _locale.value != null)
  const hasCurrentLocale = computed(() => currentLocale.value != null)
  const isLocaleRTL = computed(() => {
    if (!hasLocale.value) return false
    const shortlabel = props.locale?.shortlabel ?? _locale.value?.shortlabel ?? ''
    return RTL_LOCALES.includes(shortlabel.toLowerCase())
  })
  const dirLocale = computed(() => (isLocaleRTL.value ? 'rtl' : 'auto'))
  const displayedLocale = computed(() => currentLocale.value?.shortlabel ?? '')

  const updateLocale = (oldValue) => {
    store.commit(LANGUAGE.SWITCH_LANG, { oldValue })
  }

  return {
    _locale,
    currentLocale,
    languages,
    hasLocale,
    hasCurrentLocale,
    isLocaleRTL,
    dirLocale,
    displayedLocale,
    updateLocale
  }
}
