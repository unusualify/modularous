// hooks/useInputFetch.js
import { ref, computed } from 'vue'
import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types

import { get } from 'lodash-es'

import { makeSelectProps } from '@/hooks/utils/useSelect.js'
import { usePagination, makePaginationProps } from '@/hooks/utils/usePagination.js'
import { getURLWithoutQuery } from '@/utils/pushState'

const inflightRequests = new Map()

const fetchShared = (url) => {
  if (!inflightRequests.has(url)) {
    const request = axios.get(url)
      .finally(() => inflightRequests.delete(url))

    inflightRequests.set(url, request)
  }

  return inflightRequests.get(url)
}

export const makeInputFetchProps = propsFactory({
  ...makeSelectProps(),
  ...makePaginationProps(),
  lastPageKey: {
    type: String,
    default: 'resource.last_page'
  },
  currentPageKey: {
    type: String,
    default: 'resource.current_page'
  },
  dataKey: {
    type: String,
    default: 'resource.data'
  },
  // false: the form does not wait for this source; selected values are resolved via `?ids=`
  awaitSource: {
    type: Boolean,
    default: true
  }
})

export default function useInputFetch(props, context) {
  const {
    activePage,
    activeLastPage,
    nextPage,
    search,
    itemsLoading,
    elements,
    fullUrl,

    setItemsLoading,
    setElements,
    appendElements,
    prependElements,
    setActivePage
  } = usePagination(props, context)

  const emitSourceLoading = (value) => {
    if (!props.awaitSource) return

    context.emit('update:input', [
      {
        key: 'sourceLoading',
        value
      }
    ])
  }

  const emitLoadedSource = () => {
    context.emit('update:input', [
      ...(props.awaitSource ? [{ key: 'sourceLoading', value: false }] : []),
      {
        key: 'items',
        value: elements.value
      },
      {
        key: 'lastPage',
        value: activeLastPage.value
      },
      {
        key: 'page',
        value: activePage.value
      }
    ])
  }

  const selectedValues = () => {
    const value = context.input?.value

    if (value === null || value === undefined || value === '') return []

    return (Array.isArray(value) ? value : [value])
      .map((item) => (item !== null && typeof item === 'object') ? item[props.itemValue] : item)
      .filter((item) => item !== null && item !== undefined && item !== '' && item !== 0)
  }

  const missingSelectedValues = () => selectedValues()
    .filter((id) => !elements.value.find((o) => o[props.itemValue] == id))

  const fetchMissingSelectedItems = async () => {
    const missing = missingSelectedValues()

    if (missing.length === 0) return

    const response = await fetchShared(`${getURLWithoutQuery(props.endpoint)}?ids=${missing.join(',')}`)

    if (Array.isArray(response.data) && response.data.length > 0) {
      prependElements(response.data)
    }
  }

  const getItemsFromApi = async () => {
    if (nextPage.value > activeLastPage.value && activeLastPage.value >= 0) return

    if (!props.awaitSource && itemsLoading.value) return

    setItemsLoading(true)
    emitSourceLoading(true)

    try {
      const response = props.awaitSource
        ? await axios.get(fullUrl.value)
        : await fetchShared(fullUrl.value)

      if (response.status != 200) {
        setItemsLoading(false)
        emitSourceLoading(false)
        return
      }

      if (activeLastPage.value < 0)
        activeLastPage.value = get(response.data, props.lastPageKey, 1)

      if (search.value == '') {
        appendElements(get(response.data, props.dataKey) ?? [])
      } else {
        setElements(get(response.data, props.dataKey) ?? [])
      }

      setActivePage(get(response.data, props.currentPageKey, 1))

      if (props.awaitSource) {
        if (missingSelectedValues().length > 0 && nextPage.value <= activeLastPage.value) {
          return getItemsFromApi()
        }
      } else {
        await fetchMissingSelectedItems()
      }

      setItemsLoading(false)
      emitLoadedSource()
    } catch (error) {
      console.error('[useInputFetch]', error)
      setItemsLoading(false)
      emitSourceLoading(false)
    }
  }

  const searchOnInputFetch = (searchVal) => {
    if( !!context.input ){
      if(searchVal == ''){
        elements.value = []
        getItemsFromApi()
      }else{
        search.value = searchVal
      }

      return
    }

    search.value = searchVal
    activePage.value = 1
    nextPage.value = 1
    lastPage.value = -1

    if(search.value == ''){
      elements.value = []
    }

    getItemsFromApi()
  }

  return {
    itemsLoading,
    elements,
    getItemsFromApi,
    activePage,
    activeLastPage,
    nextPage,
    searchOnInputFetch
  }

}
