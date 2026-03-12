import { describe, expect, test } from 'vitest'
import { createStore } from 'vuex'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import useInertiaRequests, { useInertiaLoading } from '../../src/js/hooks/useInertiaRequests.js'
import configModule from '../../src/js/store/modules/config.js'

vi.mock('@/setup/inertia-interceptors', () => ({
  getActiveInertiaRequestCount: vi.fn(() => 0),
  hasActiveInertiaRequests: vi.fn(() => false)
}))

const { getActiveInertiaRequestCount, hasActiveInertiaRequests } = await import('@/setup/inertia-interceptors')

function createStoreWithConfig(overrides = {}) {
  const configState = {
    ...configModule.state,
    axiosRequestCount: 0,
    ...overrides.config
  }
  return createStore({
    modules: {
      config: { ...configModule, state: configState }
    }
  })
}

const TestComponent = defineComponent({
  setup() {
    return useInertiaRequests()
  },
  render: () => h('div')
})

const LoadingComponent = defineComponent({
  setup() {
    return useInertiaLoading()
  },
  render: () => h('div')
})

async function factory(store) {
  return mount(TestComponent, {
    global: { plugins: [store] }
  })
}

describe('useInertiaRequests', () => {
  test('returns activeRequestCount, hasActiveRequests, isLoading', async () => {
    const store = createStoreWithConfig()
    const wrapper = await factory(store)
    expect(wrapper.vm.activeRequestCount).toBe(0)
    expect(wrapper.vm.hasActiveRequests).toBe(false)
    expect(wrapper.vm.isLoading).toBe(false)
  })

  test('hasActiveRequests true when axiosRequestCount > 0', async () => {
    const store = createStoreWithConfig({ config: { axiosRequestCount: 2 } })
    const wrapper = await factory(store)
    expect(wrapper.vm.hasActiveRequests).toBe(true)
    expect(wrapper.vm.isLoading).toBe(true)
  })

  test('getRequestCount calls getActiveInertiaRequestCount', async () => {
    const store = createStoreWithConfig()
    getActiveInertiaRequestCount.mockReturnValue(3)
    const wrapper = await factory(store)
    expect(wrapper.vm.getRequestCount()).toBe(3)
    expect(getActiveInertiaRequestCount).toHaveBeenCalled()
  })

  test('hasRequests calls hasActiveInertiaRequests', async () => {
    const store = createStoreWithConfig()
    hasActiveInertiaRequests.mockReturnValue(true)
    const wrapper = await factory(store)
    expect(wrapper.vm.hasRequests()).toBe(true)
  })
})

describe('useInertiaLoading', () => {
  test('returns isLoading, loadingText, activeRequestCount', async () => {
    const store = createStoreWithConfig()
    const wrapper = mount(LoadingComponent, {
      global: { plugins: [store] }
    })
    expect(wrapper.vm.isLoading).toBeDefined()
    expect(wrapper.vm.loadingText).toBe('')
    expect(wrapper.vm.activeRequestCount).toBe(0)
  })
})
