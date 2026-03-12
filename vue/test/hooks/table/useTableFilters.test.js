import { describe, expect, test, vi, beforeEach } from 'vitest'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import i18n from '../../../src/js/config/i18n'
import useTableFilters from '../../../src/js/hooks/table/useTableFilters.js'

beforeEach(() => {
  Object.defineProperty(window, 'location', {
    value: new URL('https://example.com/posts'),
    writable: true
  })
  vi.spyOn(Storage.prototype, 'getItem').mockReturnValue(null)
})

const TestComponent = defineComponent({
  props: {
    filterList: { type: Array, default: () => [] },
    searchInitialValue: { type: String, default: '' },
    navActive: { type: String, default: 'all' }
  },
  setup(props) {
    return useTableFilters(props)
  },
  render: () => h('div')
})

async function factory(props = {}) {
  return mount(TestComponent, {
    global: { plugins: [i18n] },
    props: { filterList: [], ...props }
  })
}

describe('useTableFilters', () => {
  test('returns search, searchModel, activeFilterSlug, setSearchValue', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.search).toBeDefined()
    expect(wrapper.vm.searchModel).toBeDefined()
    expect(wrapper.vm.activeFilterSlug).toBeDefined()
    expect(typeof wrapper.vm.setSearchValue).toBe('function')
  })

  test('setSearchValue updates search when value differs', async () => {
    const wrapper = await factory({ searchInitialValue: 'foo' })
    const result = wrapper.vm.setSearchValue('bar')
    expect(result).toBe(true)
    expect(wrapper.vm.search).toBe('bar')
  })

  test('setSearchValue returns false when value same', async () => {
    const wrapper = await factory({ searchInitialValue: 'foo' })
    const result = wrapper.vm.setSearchValue('foo')
    expect(result).toBe(false)
  })

  test('setFilterSlug updates activeFilterSlug', async () => {
    const wrapper = await factory({
      filterList: [{ slug: 'all', name: 'All' }, { slug: 'active', name: 'Active' }]
    })
    const result = wrapper.vm.setFilterSlug('active')
    expect(result).toBe(true)
    expect(wrapper.vm.activeFilterSlug).toBe('active')
  })

  test('setMainFilters updates mainFilters', async () => {
    const wrapper = await factory()
    const newFilters = [{ slug: 'draft', name: 'Draft' }]
    wrapper.vm.setMainFilters(newFilters)
    expect(wrapper.vm.mainFilters).toEqual(newFilters)
  })
})
