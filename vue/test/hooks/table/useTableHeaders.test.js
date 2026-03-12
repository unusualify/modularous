import { describe, expect, test, vi, beforeEach } from 'vitest'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import useTableHeaders from '../../../src/js/hooks/table/useTableHeaders.js'

const TestComponent = defineComponent({
  props: {
    columns: { type: Array, default: () => [] },
    hideHeaders: { type: Boolean, default: false },
    customRow: { type: Object, default: () => ({}) }
  },
  setup(props) {
    return useTableHeaders(props)
  },
  render: () => h('div')
})

beforeEach(() => {
  vi.spyOn(Storage.prototype, 'getItem').mockReturnValue(null)
  vi.spyOn(Storage.prototype, 'setItem').mockImplementation(() => {})
  Object.defineProperty(window, 'location', {
    value: { pathname: '/test/path' },
    writable: true
  })
})

async function factory(props = {}) {
  return mount(TestComponent, {
    props: {
      columns: [
        { key: 'name', title: 'Name', visible: true },
        { key: 'email', title: 'Email', visible: true, searchable: true }
      ],
      ...props
    }
  })
}

describe('useTableHeaders', () => {
  test('returns headers, headersModel, selectedHeaders', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.headers).toBeDefined()
    expect(wrapper.vm.headersModel).toBeDefined()
    expect(wrapper.vm.selectedHeaders).toBeDefined()
  })

  test('filters headers by visible columns', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.headers.length).toBe(2)
  })

  test('hasSearchableHeader is true when column has searchable', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.hasSearchableHeader).toBe(true)
  })

  test('applyHeaders syncs headers with model and persists hidden to localStorage', async () => {
    const wrapper = await factory()
    const setItemSpy = vi.spyOn(Storage.prototype, 'setItem')
    wrapper.vm.headersModel[0].visible = false
    wrapper.vm.applyHeaders()
    expect(setItemSpy).toHaveBeenCalledWith('table_unvisible_columns_/test/path', 'name')
  })

  test('removeHeader hides column and persists to localStorage', async () => {
    const wrapper = await factory()
    const setItemSpy = vi.spyOn(Storage.prototype, 'setItem')
    wrapper.vm.removeHeader('email')
    expect(setItemSpy).toHaveBeenCalled()
    expect(wrapper.vm.headers.some(h => h.key === 'email')).toBe(false)
  })
})
