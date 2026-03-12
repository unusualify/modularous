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
