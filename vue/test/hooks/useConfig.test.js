import { describe, expect, test } from 'vitest'
import { createStore } from 'vuex'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import useConfig from '../../src/js/hooks/useConfig.js'
import configModule from '../../src/js/store/modules/config.js'
import ambientModule from '../../src/js/store/modules/ambient.js'

function createStoreWithOverrides(overrides = {}) {
  const configState = {
    ...configModule.state,
    isInertia: false,
    isRequestInProgress: false,
    ...overrides.config
  }
  const ambientState = {
    ...ambientModule.state,
    isHot: false,
    appName: 'TestApp',
    appEnv: 'testing',
    ...overrides.ambient
  }
  return createStore({
    modules: {
      config: { ...configModule, state: configState },
      ambient: { ...ambientModule, state: ambientState }
    }
  })
}

const TestComponent = defineComponent({
  setup() {
    const config = useConfig()
    return config
  },
  render: () => h('div')
})

async function factory(store) {
  return mount(TestComponent, {
    global: { plugins: [store] }
  })
}

describe('useConfig', () => {
  test('returns isHot from ambient state', async () => {
    const store = createStoreWithOverrides({ ambient: { isHot: true } })
    const wrapper = await factory(store)
    expect(wrapper.vm.isHot).toBe(true)
  })

  test('returns appName from ambient state', async () => {
    const store = createStoreWithOverrides({ ambient: { appName: 'MyApp' } })
    const wrapper = await factory(store)
    expect(wrapper.vm.appName).toBe('MyApp')
  })

  test('returns appEnv from ambient state', async () => {
    const store = createStoreWithOverrides({ ambient: { appEnv: 'production' } })
    const wrapper = await factory(store)
    expect(wrapper.vm.appEnv).toBe('production')
  })

  test('returns shouldUseInertia from config state', async () => {
    const store = createStoreWithOverrides({ config: { isInertia: true } })
    const wrapper = await factory(store)
    expect(wrapper.vm.shouldUseInertia).toBe(true)
  })

  test('returns isRequestInProgress from config state', async () => {
    const store = createStoreWithOverrides({ config: { isRequestInProgress: true } })
    const wrapper = await factory(store)
    expect(wrapper.vm.isRequestInProgress).toBe(true)
  })

  test('setRequestInProgress commits mutation', async () => {
    const store = createStoreWithOverrides()
    const spy = vi.spyOn(store, 'commit')
    const wrapper = await factory(store)
    wrapper.vm.setRequestInProgress(true)
    expect(spy).toHaveBeenCalledWith('__setRequestInProgress', true)
  })

  test('increaseAxiosRequest commits mutation', async () => {
    const store = createStoreWithOverrides()
    const spy = vi.spyOn(store, 'commit')
    const wrapper = await factory(store)
    wrapper.vm.increaseAxiosRequest()
    expect(spy).toHaveBeenCalledWith('__increaseAxiosRequest')
  })

  test('decreaseAxiosRequest commits mutation', async () => {
    const store = createStoreWithOverrides()
    const spy = vi.spyOn(store, 'commit')
    const wrapper = await factory(store)
    wrapper.vm.decreaseAxiosRequest()
    expect(spy).toHaveBeenCalledWith('__decreaseAxiosRequest')
  })
})
