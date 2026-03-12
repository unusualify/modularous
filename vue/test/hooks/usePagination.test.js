import { describe, expect, test } from 'vitest'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import { usePagination } from '../../src/js/hooks/utils/usePagination.js'

const TestComponent = defineComponent({
  props: {
    endpoint: { type: String, default: '/api/items' },
    page: { type: Number, default: 1 },
    lastPage: { type: Number, default: -1 },
    itemsPerPage: { type: Number, default: 20 },
    items: { type: Array, default: () => [] }
  },
  setup(props, context) {
    return usePagination(props, context)
  },
  render: () => h('div')
})

async function factory(props = {}) {
  return mount(TestComponent, {
    props: { endpoint: 'https://example.com/api/items', ...props }
  })
}

describe('usePagination', () => {
  test('returns pagination state and methods', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.activePage).toBe(1)
    expect(wrapper.vm.searchModel).toBe('')
    expect(typeof wrapper.vm.setActivePage).toBe('function')
    expect(typeof wrapper.vm.setActiveLastPage).toBe('function')
    expect(typeof wrapper.vm.setElements).toBe('function')
    expect(typeof wrapper.vm.appendElements).toBe('function')
    expect(typeof wrapper.vm.prependElements).toBe('function')
  })

  test('setActivePage updates activePage and nextPage', async () => {
    const wrapper = await factory({ page: 1 })
    wrapper.vm.setActivePage(3)
    expect(wrapper.vm.activePage).toBe(3)
    expect(wrapper.vm.nextPage).toBe(4)
  })

  test('setActiveLastPage updates activeLastPage', async () => {
    const wrapper = await factory()
    wrapper.vm.setActiveLastPage(10)
    expect(wrapper.vm.activeLastPage).toBe(10)
  })

  test('setElements replaces elements', async () => {
    const wrapper = await factory({ items: [1, 2] })
    wrapper.vm.setElements([3, 4, 5])
    expect(wrapper.vm.elements).toEqual([3, 4, 5])
  })

  test('appendElements concatenates to elements', async () => {
    const wrapper = await factory({ items: [1, 2] })
    wrapper.vm.appendElements([3, 4])
    expect(wrapper.vm.elements).toEqual([1, 2, 3, 4])
  })

  test('prependElements prepends to elements', async () => {
    const wrapper = await factory({ items: [3, 4] })
    wrapper.vm.prependElements([1, 2])
    expect(wrapper.vm.elements).toEqual([1, 2, 3, 4])
  })

  test('searchFilterObject includes search when searchModel has value', async () => {
    const wrapper = await factory()
    wrapper.vm.searchModel = 'query'
    await wrapper.vm.$nextTick()
    expect(wrapper.vm.searchFilterObject).toEqual({ search: 'query' })
  })

  test('fullUrl includes query parameters', async () => {
    const wrapper = await factory({ endpoint: 'https://example.com/api/items' })
    expect(wrapper.vm.fullUrl).toContain('itemsPerPage=20')
  })
})
