import { describe, expect, test, vi, beforeEach } from 'vitest'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import useTableState from '../../../src/js/hooks/table/useTableState.js'

beforeEach(() => {
  Object.defineProperty(window, 'location', {
    value: new URL('https://example.com/posts?page=2'),
    writable: true
  })
  vi.spyOn(Storage.prototype, 'getItem').mockReturnValue(null)
  vi.spyOn(Storage.prototype, 'setItem').mockImplementation(() => {})
})

const TestComponent = defineComponent({
  setup(props, context) {
    return useTableState(props, context)
  },
  render: () => h('div')
})

async function factory() {
  return mount(TestComponent)
}

describe('useTableState', () => {
  test('returns getQueryParameters, getLastParameters, setLastParameters', async () => {
    const wrapper = await factory()
    expect(typeof wrapper.vm.getQueryParameters).toBe('function')
    expect(typeof wrapper.vm.getLastParameters).toBe('function')
    expect(typeof wrapper.vm.setLastParameters).toBe('function')
  })

  test('getQueryParameters parses URL search params', async () => {
    const wrapper = await factory()
    const params = wrapper.vm.getQueryParameters()
    expect(params).toBeDefined()
    expect(typeof params).toBe('object')
  })

  test('getLastParameters merges cached with current query', async () => {
    Storage.prototype.getItem.mockReturnValue(JSON.stringify({ page: 1, itemsPerPage: 20 }))
    const wrapper = await factory()
    const last = wrapper.vm.getLastParameters()
    expect(last).toBeDefined()
    expect(last).toHaveProperty('page')
    expect(last).toHaveProperty('itemsPerPage')
  })

  test('setLastParameters stores to localStorage', async () => {
    const setItemSpy = vi.spyOn(Storage.prototype, 'setItem')
    const wrapper = await factory()
    wrapper.vm.setLastParameters({ page: 3, itemsPerPage: 20 })
    expect(setItemSpy).toHaveBeenCalled()
  })
})
