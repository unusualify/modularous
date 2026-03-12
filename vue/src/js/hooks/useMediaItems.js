/**
 * useMediaItems - Composable replacement for mediaItemsMixin.
 * Provides media selection state and helpers from Vuex mediaLibrary store.
 */
import { computed } from 'vue'
import { useStore } from 'vuex'

export default function useMediaItems (props, emit) {
  const store = useStore()
  const selectedItems = computed(() => props.selectedItems ?? [])
  const usedItems = computed(() => props.usedItems ?? [])

  const itemsLoading = computed(() => store.state.mediaLibrary?.loading ?? [])
  const replacingMediaIds = computed(() => {
    const loading = itemsLoading.value
    return (Array.isArray(loading) ? loading : []).reduce((agg, curr) => {
      if (curr?.isReplacement) {
        agg[curr.replacementId] = curr.id
      }
      return agg
    }, {})
  })

  const isSelected = (item, keys = ['id']) => {
    return Boolean(selectedItems.value?.find(sItem => keys.every(key => sItem[key] === item[key])))
  }
  const isUsed = (item, keys = ['id']) => {
    return Boolean(usedItems.value?.find(uItem => keys.every(key => uItem[key] === item[key])))
  }
  const toggleSelection = (item) => {
    emit('change', item)
  }
  const shiftToggleSelection = (item) => {
    emit('shiftChange', item, true)
  }

  return {
    itemsLoading,
    replacingMediaIds,
    isSelected,
    isUsed,
    toggleSelection,
    shiftToggleSelection
  }
}
