// hooks/table/useTableGroup.js
import { computed, watch } from 'vue'
import _ from 'lodash-es'

/**
 * @param {unknown} order
 * @returns {'asc'|'desc'}
 */
export function normalizeGroupOrder (order) {
  if (order === 'desc' || order === 'asc') {
    return order
  }
  return 'asc'
}

/**
 * @param {unknown} raw
 * @returns {{ key: string, order: string } | null}
 */
export function parseGroupByConfigItem (raw) {
  if (typeof raw === 'string' && raw.length > 0) {
    return { key: raw, order: 'asc' }
  }
  if (raw && typeof raw === 'object' && typeof raw.key === 'string' && raw.key.length > 0) {
    const order = raw.order === 'desc' || raw.order === 'asc' ? raw.order : 'asc'
    return { key: raw.key, order }
  }
  return null
}

/**
 * Normalizes legacy `['col_a', 'col_b']` or `[{ key, order }, ...]` shapes (tests / external).
 *
 * @param {unknown[]} list
 * @returns {{ key: string, order: string }[]}
 */
export function normalizeGroupByConfig (list) {
  if (!Array.isArray(list)) {
    return []
  }
  const seen = new Set()
  const out = []
  for (const item of list) {
    const parsed = parseGroupByConfigItem(item)
    if (parsed && !seen.has(parsed.key)) {
      seen.add(parsed.key)
      out.push(parsed)
    }
  }
  return out
}

/**
 * Columns with `groupable: true` drive client-side grouping (per-header toggle).
 * `groupOrder` on the column (asc|desc) is applied when that column is grouped.
 *
 * @param {import('vue').Ref<object>} optionsRef
 * @param {object} props
 */
function useTableGroup (props, optionsRef) {
  const columnKeys = computed(() =>
    (props.columns ?? []).map((c) => c.key).filter(Boolean)
  )

  /** One entry per column where `groupable === true` (default false from backend merge). */
  const groupByEntries = computed(() => {
    const keys = new Set(columnKeys.value)
    const out = []
    const seen = new Set()
    for (const col of props.columns ?? []) {
      if (!col || col.groupable !== true || !col.key) {
        continue
      }
      if (!keys.has(col.key) || seen.has(col.key)) {
        continue
      }
      seen.add(col.key)
      out.push({
        key: col.key,
        order: normalizeGroupOrder(col.groupOrder),
      })
    }
    return out
  })

  const groupKeys = computed(() => groupByEntries.value.map((e) => e.key))

  const hasGroupableColumns = computed(() => groupKeys.value.length > 0)

  const selectedGroupKey = computed({
    get () {
      const gb = optionsRef.value.groupBy
      if (!Array.isArray(gb) || gb.length === 0) {
        return null
      }
      const first = gb[0]
      const key = typeof first === 'object' && first !== null ? first.key : first
      return groupKeys.value.includes(key) ? key : null
    },
    set (val) {
      if (val == null || val === '') {
        optionsRef.value.groupBy = []
      } else {
        const entry = groupByEntries.value.find((e) => e.key === val)
        optionsRef.value.groupBy = [{ key: val, order: entry?.order ?? 'asc' }]
      }
    },
  })

  const isGroupingActive = computed(
    () => Array.isArray(optionsRef.value.groupBy) && optionsRef.value.groupBy.length > 0
  )

  function isGroupActiveForKey (key) {
    return selectedGroupKey.value === key
  }

  function toggleGroupByColumn (key) {
    if (!groupKeys.value.includes(key)) {
      return
    }
    if (selectedGroupKey.value === key) {
      optionsRef.value.groupBy = []
      return
    }
    const entry = groupByEntries.value.find((e) => e.key === key)
    optionsRef.value.groupBy = [{ key, order: entry?.order ?? 'asc' }]
  }

  function clearGroupBy () {
    optionsRef.value.groupBy = []
  }

  const groupLabelForKey = (key) => {
    const col = (props.columns ?? []).find((c) => c.key === key)
    return col?.title ?? col?.key ?? key
  }

  watch(groupKeys, (keys) => {
    const current = selectedGroupKey.value
    if (current && !keys.includes(current)) {
      optionsRef.value.groupBy = []
    }
  })

  return {
    groupKeys,
    /** @deprecated use hasGroupableColumns */
    hasGroupMenu: hasGroupableColumns,
    hasGroupableColumns,
    selectedGroupKey,
    groupLabelForKey,
    isGroupingActive,
    isGroupActiveForKey,
    toggleGroupByColumn,
    clearGroupBy,
  }
}

/**
 * Options that affect the index API payload (excludes `groupBy` — client-side only).
 * Vuetify's `update:options` includes many extra keys (`mustSort`, `groupDesc`, …).
 *
 * @param {object|undefined} o
 * @returns {{ itemsPerPage: *, page: *, sortBy: unknown[], search: string }}
 */
export function pickFetchRelevantOptions (o) {
  if (!o || typeof o !== 'object') {
    return { itemsPerPage: undefined, page: undefined, sortBy: [], search: '' }
  }
  return {
    itemsPerPage: o.itemsPerPage,
    page: o.page,
    sortBy: Array.isArray(o.sortBy) ? o.sortBy : [],
    search: o.search ?? '',
  }
}

/**
 * Returns true when `newOpts` differs from `oldOpts` only in `groupBy`.
 * Used to avoid redundant index API calls when toggling client-side grouping.
 *
 * @param {object|undefined} oldOpts
 * @param {object|undefined} newOpts
 * @returns {boolean}
 */
export function onlyGroupByChanged (oldOpts, newOpts) {
  if (!oldOpts || !newOpts) {
    return false
  }
  const fetchSame = _.isEqual(
    pickFetchRelevantOptions(oldOpts),
    pickFetchRelevantOptions(newOpts)
  )
  const groupByChanged = !_.isEqual(
    Array.isArray(oldOpts.groupBy) ? oldOpts.groupBy : [],
    Array.isArray(newOpts.groupBy) ? newOpts.groupBy : []
  )
  return fetchSame && groupByChanged
}

export default useTableGroup

/** Alias for {@link useTableGroup}. */
export const useGroup = useTableGroup
