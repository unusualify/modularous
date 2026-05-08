import { ref, computed, watch, unref } from 'vue'
import axios from 'axios'
import { useJsonDiff, jsonDiffPartClass } from '@/hooks'

/**
 * Same logical id regardless of number vs string from JSON / emit.
 *
 * @param {unknown} a
 * @param {unknown} b
 * @returns {boolean}
 */
export function revisionIdEq(a, b) {
  if (a == null || b == null) return a === b
  return String(a) === String(b)
}

/**
 * @param {string|number|Date} dateString
 * @returns {string}
 */
export function formatRevisionDate(dateString) {
  const date = new Date(dateString)
  return new Intl.DateTimeFormat(undefined, {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    hour12: false,
  }).format(date)
}

/**
 * @param {import('diff').Change} part
 * @param {string} [baseClass='revision-diff-part']
 * @returns {string}
 */
export function revisionDiffPartClass(part, baseClass = 'revision-diff-part') {
  return jsonDiffPartClass(part, baseClass)
}

/**
 * Revision snapshot diff: loads two payloads via restore route `?preview=1&revisionId=`.
 *
 * @param {object} options
 * @param {import('vue').Ref<string>} options.recordId
 * @param {import('vue').Ref<unknown>} options.targetRevisionId — revision being inspected (newer side of diff)
 * @param {import('vue').Ref<string>|string|import('vue').ComputedRef<string>} options.restoreEndpoint — URL with `:id` placeholder
 * @param {import('vue').ComputedRef<Array>} options.sortedRevisions — newest first
 * @param {import('vue').Ref<boolean>} options.previewDialogActive
 * @param {import('vue').Ref<string>} options.previewTab — `'preview'` | `'diff'`
 * @param {(error: unknown) => void} [options.onDiffError] — e.g. toast / alert
 */
export default function useRevisionDiff(options) {
  const {
    recordId,
    targetRevisionId,
    restoreEndpoint,
    sortedRevisions,
    previewDialogActive,
    previewTab,
    onDiffError,
  } = options

  const compareBaseRevisionId = ref(null)
  const diffLoading = ref(false)

  const jsonDiff = useJsonDiff({ baseClass: 'revision-diff-part' })
  const { lineParts: diffLineParts, computeFromValues, clear: clearJsonDiff, diffPartClass } = jsonDiff

  const compareCandidates = computed(() => {
    const list = sortedRevisions.value
    const idx = list.findIndex((r) => revisionIdEq(r.id, targetRevisionId.value))
    if (idx === -1) return []
    return list.slice(idx + 1)
  })

  const compareSelectItems = computed(() =>
    compareCandidates.value.map((r) => ({
      title: `${r.label} · ${formatRevisionDate(r.datetime)}`,
      value: r.id,
    }))
  )

  const effectiveCompareBaseId = computed(() => {
    if (compareBaseRevisionId.value != null && compareBaseRevisionId.value !== '') {
      return compareBaseRevisionId.value
    }
    return compareCandidates.value[0]?.id ?? null
  })

  function resolveEndpoint() {
    return unref(restoreEndpoint)
  }

  async function loadRevisionDiff() {
    const id = recordId.value
    const target = targetRevisionId.value
    if (!id || target == null || target === '') return

    const baseId = effectiveCompareBaseId.value
    if (baseId == null || revisionIdEq(baseId, target)) {
      clearJsonDiff()
      return
    }

    diffLoading.value = true
    try {
      const url = resolveEndpoint().replace(':id', id)
      const [resBase, resTarget] = await Promise.all([
        axios.get(url, { params: { revisionId: baseId, preview: 1 } }),
        axios.get(url, { params: { revisionId: target, preview: 1 } }),
      ])
      computeFromValues(resBase.data?.form_fields ?? {}, resTarget.data?.form_fields ?? {})
    } catch (e) {
      clearJsonDiff()
      if (typeof onDiffError === 'function') {
        onDiffError(e)
      }
    } finally {
      diffLoading.value = false
    }
  }

  function resetDiffState() {
    compareBaseRevisionId.value = null
    clearJsonDiff()
  }

  watch(
    [previewTab, targetRevisionId, compareBaseRevisionId],
    () => {
      if (!previewDialogActive.value || previewTab.value !== 'diff') return
      const candidates = compareCandidates.value
      if (!candidates.length) {
        clearJsonDiff()
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
      loadRevisionDiff()
    }
  )

  return {
    compareBaseRevisionId,
    diffLoading,
    diffLineParts,
    compareCandidates,
    compareSelectItems,
    effectiveCompareBaseId,
    loadRevisionDiff,
    resetDiffState,
    diffPartClass,
  }
}
