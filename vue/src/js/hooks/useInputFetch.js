// hooks/useInputFetch.js
import { ref, computed } from 'vue'
import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types

import { get } from 'lodash-es'

import { makeSelectProps } from '@/hooks/utils/useSelect.js'
import { usePagination, makePaginationProps } from '@/hooks/utils/usePagination.js'


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
    setActivePage
  } = usePagination(props, context)

  const getItemsFromApi = async () => {

    if( !(nextPage.value > activeLastPage.value) || activeLastPage.value < 0){
      setItemsLoading(true)

      context.emit('update:input', [
        {
          key: 'sourceLoading',
          value: true
        }
      ])

      return new Promise(() => {
        axios.get(fullUrl.value)
          .then(response => {
            if(response.status == 200){

              if(activeLastPage.value < 0)
                activeLastPage.value = get(response.data, props.lastPageKey, 1)

              if(search.value == ''){
                appendElements(get(response.data, props.dataKey) ?? []);
              }else{
                setElements(get(response.data, props.dataKey) ?? [])
              }
              // page.value++;

              setActivePage(get(response.data, props.currentPageKey, 1))

              if(!!context.input.value){
                let searchContinue = false;

                if(context.input.value){

                  if(props.multiple){
                    context.input.value.forEach(function(id){
                      if(!elements.value.find((o) => o[props.itemValue] == id)){
                        searchContinue = true
                        return false
                      }
                    })
                  }else {
                    searchContinue = !elements.value.find((o) => o[props.itemValue] == context.input.value)
                  }
                }

                if(searchContinue){
                  getItemsFromApi()
                } else {
                  setItemsLoading(false)
                  context.emit('update:input', [
                    {
                      key: 'sourceLoading',
                      value: false
                    },
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
              }else{
                setItemsLoading(false)
                context.emit('update:input', [
                  {
                    key: 'sourceLoading',
                    value: false
                  },
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

            }
          })
      })
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
