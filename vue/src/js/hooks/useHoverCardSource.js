import { computed, ref, watch } from 'vue'
import { isObject } from '@/utils/helpers'
import { loadHoverCardItem, isHoverCardValueBlank } from '@/utils/hoverCard'

/**
 * Resolve the record a hover card describes: an inline `item` object is used as-is;
 * a scalar `item` plus `endpoint` is fetched (batched + cached) via `loadHoverCardItem`.
 *
 * @param {{ item: Object|string|number|null, endpoint: string|null, params: Object, itemKey: string }} props
 */
export default function useHoverCardSource (props) {
  const fetched = ref(null)
  const loading = ref(false)

  const remoteId = computed(() => {
    if (!props.endpoint || isObject(props.item) || isHoverCardValueBlank(props.item)) {
      return null
    }
    return props.item
  })

  let token = 0

  watch(
    () => [props.endpoint, remoteId.value, JSON.stringify(props.params ?? {}), props.itemKey],
    async () => {
      const current = ++token
      fetched.value = null

      if (remoteId.value === null) {
        loading.value = false
        return
      }

      loading.value = true
      const record = await loadHoverCardItem(props.endpoint, remoteId.value, {
        params: props.params,
        itemKey: props.itemKey
      })

      if (current === token) {
        fetched.value = record
        loading.value = false
      }
    },
    { immediate: true }
  )

  const resolvedItem = computed(() => isObject(props.item) ? props.item : fetched.value)

  return { resolvedItem, loading }
}
