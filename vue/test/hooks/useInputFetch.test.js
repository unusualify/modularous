import { describe, expect, test, vi, beforeEach } from 'vitest'
import { defineComponent, ref } from 'vue'
import { mount } from '@vue/test-utils'
import useInputFetch, { makeInputFetchProps } from '../../src/js/hooks/useInputFetch.js'

const mockAxiosResponse = {
  status: 200,
  data: {
    resource: {
      data: [{ id: 1, name: 'Item 1' }],
      last_page: 1,
      current_page: 1
    }
  }
}

beforeEach(() => {
  window.axios = { get: vi.fn().mockResolvedValue(mockAxiosResponse) }
})

const TestComponent = defineComponent({
  props: {
    endpoint: { type: String, default: '/api/items' },
    page: { type: Number, default: 1 },
    items: { type: Array, default: () => [] },
    ...makeInputFetchProps()
  },
  emits: ['update:input'],
  setup(props, context) {
    const inputRef = ref(null)
    const ctx = { ...context, input: inputRef }
    return useInputFetch(props, ctx)
  },
  template: '<div />'
})

async function factory(props = {}) {
  return mount(TestComponent, {
    props: { endpoint: 'https://example.com/api/items', ...props }
  })
}

describe('useInputFetch', () => {
  test('returns pagination state and getItemsFromApi', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.activePage).toBeDefined()
    expect(wrapper.vm.elements).toBeDefined()
    expect(typeof wrapper.vm.getItemsFromApi).toBe('function')
    expect(typeof wrapper.vm.searchOnInputFetch).toBe('function')
    expect(wrapper.vm.activeLastPage).toBeDefined()
    expect(wrapper.vm.nextPage).toBeDefined()
  })

  test('makeInputFetchProps merges select and pagination props', () => {
    const props = makeInputFetchProps()
    expect(props.itemValue).toBeDefined()
    expect(props.endpoint).toBeDefined()
    expect(props.page).toBeDefined()
    expect(props.itemsPerPage).toBeDefined()
  })

  test('getItemsFromApi triggers axios fetch', async () => {
    const wrapper = await factory()
    wrapper.vm.getItemsFromApi()
    await new Promise(r => setTimeout(r, 50))
    expect(window.axios.get).toHaveBeenCalled()
  })

  test('getItemsFromApi emits update:input with sourceLoading true', async () => {
    const wrapper = await factory()
    wrapper.vm.getItemsFromApi()
    await wrapper.vm.$nextTick()
    expect(wrapper.emitted('update:input')).toBeTruthy()
    const firstEmit = wrapper.emitted('update:input')[0]
    expect(firstEmit[0]).toContainEqual({ key: 'sourceLoading', value: true })
  })

  test('searchOnInputFetch with empty string triggers getItemsFromApi', async () => {
    const wrapper = await factory()
    wrapper.vm.searchOnInputFetch('')
    await new Promise(r => setTimeout(r, 50))
    expect(window.axios.get).toHaveBeenCalled()
  })

  test('searchOnInputFetch with non-empty string, when context.input exists, does not call getItemsFromApi', async () => {
    const wrapper = await factory()
    window.axios.get.mockClear()
    wrapper.vm.searchOnInputFetch('query')
    expect(window.axios.get).not.toHaveBeenCalled()
  })

  test('searchOnInputFetch with empty string clears elements', async () => {
    const wrapper = await factory({ items: [{ id: 1 }] })
    wrapper.vm.searchOnInputFetch('')
    expect(wrapper.vm.elements).toEqual([])
  })
})

const page = (data, currentPage, lastPage) => ({
  status: 200,
  data: { resource: { data, current_page: currentPage, last_page: lastPage } }
})

const makeProps = (overrides = {}) => ({
  endpoint: 'http://app.test/countries?light=1',
  page: 1,
  lastPage: -1,
  itemsPerPage: 2,
  items: [],
  with: [],
  itemValue: 'id',
  multiple: true,
  lastPageKey: 'resource.last_page',
  currentPageKey: 'resource.current_page',
  dataKey: 'resource.data',
  awaitSource: true,
  ...overrides
})

const makeContext = (value) => ({
  emit: vi.fn(),
  input: ref(value)
})

const emittedKeys = (context) => context.emit.mock.calls
  .flatMap(([, payload]) => payload.map((item) => `${item.key}:${item.key === 'sourceLoading' ? item.value : '*'}`))

describe('useInputFetch awaitSource', () => {
  let axiosGet

  beforeEach(() => {
    axiosGet = vi.fn()
    window.axios = { get: axiosGet }
  })

  test('awaitSource (default) blocks the form and walks pages until selected values are found', async () => {
    axiosGet
      .mockResolvedValueOnce(page([{ id: 1 }, { id: 2 }], 1, 2))
      .mockResolvedValueOnce(page([{ id: 3 }], 2, 2))

    const context = makeContext([3])
    const fetch = useInputFetch(makeProps(), context)

    await fetch.getItemsFromApi()
    await vi.waitFor(() => expect(fetch.itemsLoading.value).toBe(false))

    expect(axiosGet).toHaveBeenCalledTimes(2)
    expect(fetch.elements.value.map((item) => item.id)).toEqual([1, 2, 3])
    expect(emittedKeys(context)[0]).toBe('sourceLoading:true')
    expect(emittedKeys(context)).toContain('sourceLoading:false')
  })

  test('awaitSource false never emits sourceLoading and resolves selected values via ids', async () => {
    axiosGet
      .mockResolvedValueOnce(page([{ id: 1 }, { id: 2 }], 1, 2))
      .mockResolvedValueOnce({ status: 200, data: [{ id: 3 }] })

    const context = makeContext([3])
    const fetch = useInputFetch(makeProps({ awaitSource: false }), context)

    await fetch.getItemsFromApi()

    expect(axiosGet).toHaveBeenCalledTimes(2)
    expect(axiosGet.mock.calls[1][0]).toBe('http://app.test/countries?ids=3')
    expect(fetch.elements.value.map((item) => item.id)).toEqual([3, 1, 2])
    expect(emittedKeys(context).some((key) => key.startsWith('sourceLoading'))).toBe(false)
    expect(emittedKeys(context)).toContain('items:*')
  })

  test('awaitSource false releases loading when the request fails', async () => {
    axiosGet.mockRejectedValueOnce(new Error('network'))
    const consoleError = vi.spyOn(console, 'error').mockImplementation(() => {})

    const context = makeContext([])
    const fetch = useInputFetch(makeProps({ awaitSource: false }), context)

    await fetch.getItemsFromApi()

    expect(fetch.itemsLoading.value).toBe(false)
    expect(context.emit).not.toHaveBeenCalled()

    consoleError.mockRestore()
  })
})
