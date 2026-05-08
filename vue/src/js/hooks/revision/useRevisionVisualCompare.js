import { ref, watch, unref } from 'vue'
import axios from 'axios'
import { revisionIdEq } from './useRevisionDiff'

/**
 * Side-by-side HTML previews for two revisions (showView PUT × 2).
 * Shares `compareBaseRevisionId` / `compareCandidates` with `useRevisionDiff` (older base on the left).
 *
 * @param {object} options
 * @param {import('vue').Ref<string>} options.recordId
 * @param {import('vue').Ref<unknown>} options.targetRevisionId — newer revision (right pane)
 * @param {import('vue').Ref<string>|string|import('vue').ComputedRef<string>} options.showEndpoint — URL with `:id`
 * @param {import('vue').Ref<unknown>} options.compareBaseRevisionId
 * @param {import('vue').ComputedRef<Array>} options.compareCandidates
 * @param {import('vue').ComputedRef<unknown>} options.effectiveCompareBaseId
 * @param {import('vue').Ref<boolean>} options.previewDialogActive
 * @param {import('vue').Ref<string>} options.previewTab — `'compare'` triggers load
 * @param {import('vue').Ref<string>|import('vue').ComputedRef<string>|string} [options.activeLanguage] — forwarded to showView (Laravel locale)
 * @param {(error: unknown) => void} [options.onError]
 */
export default function useRevisionVisualCompare(options) {
  const {
    recordId,
    targetRevisionId,
    showEndpoint,
    compareBaseRevisionId,
    compareCandidates,
    effectiveCompareBaseId,
    previewDialogActive,
    previewTab,
    activeLanguage,
    onError,
  } = options

  const compareHtmlLeft = ref('')
  const compareHtmlRight = ref('')
  const compareLoading = ref(false)

  async function loadCompareVisuals() {
    const id = recordId.value
    const target = targetRevisionId.value
    if (!id || target == null || target === '') return

    const baseId = effectiveCompareBaseId.value
    if (baseId == null || revisionIdEq(baseId, target)) {
      compareHtmlLeft.value = ''
      compareHtmlRight.value = ''
      return
    }

    const url = unref(showEndpoint).replace(':id', id)
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    const lang = activeLanguage != null ? unref(activeLanguage) : ''
    const localeParams = lang ? { activeLanguage: lang } : {}

    compareLoading.value = true
    try {
      const putOpts = {
        responseType: 'text',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          Accept: 'text/html',
        },
      }
      const [resLeft, resRight] = await Promise.all([
        axios.put(url, { _token: token }, { ...putOpts, params: { revisionId: baseId, ...localeParams } }),
        axios.put(url, { _token: token }, { ...putOpts, params: { revisionId: target, ...localeParams } }),
      ])
      compareHtmlLeft.value = typeof resLeft.data === 'string' ? resLeft.data : ''
      compareHtmlRight.value = typeof resRight.data === 'string' ? resRight.data : ''
    } catch (e) {
      compareHtmlLeft.value = ''
      compareHtmlRight.value = ''
      if (typeof onError === 'function') {
        onError(e)
      }
    } finally {
      compareLoading.value = false
    }
  }

  function resetCompareVisuals() {
    compareHtmlLeft.value = ''
    compareHtmlRight.value = ''
  }

  const compareWatchSources = [previewTab, targetRevisionId, compareBaseRevisionId]
  if (activeLanguage != null) {
    compareWatchSources.push(activeLanguage)
  }

  watch(
    compareWatchSources,
    () => {
      if (!previewDialogActive.value || previewTab.value !== 'compare') return
      const candidates = compareCandidates.value
      if (!candidates.length) {
        resetCompareVisuals()
        return
      }
      const current = compareBaseRevisionId.value
      const stillValid =
        current != null
        && current !== ''
        && candidates.some((r) => revisionIdEq(r.id, current))
      if (!stillValid) {
        compareBaseRevisionId.value = candidates[0].id
      }
      loadCompareVisuals()
    }
  )

  return {
    compareHtmlLeft,
    compareHtmlRight,
    compareLoading,
    loadCompareVisuals,
    resetCompareVisuals,
  }
}
