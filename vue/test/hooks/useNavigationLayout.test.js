import { describe, expect, test, vi } from 'vitest'
import { createStore } from 'vuex'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import useNavigationLayout from '../../src/js/hooks/useNavigationLayout.js'
import configModule from '../../src/js/store/modules/config.js'

const vuetify = createVuetify({ components, directives })

function createStoreWithConfig(overrides = {}) {
  const configState = {
    ...configModule.state,
    topbarOptions: { enabled: true, showOnMobile: true, showOnDesktop: true },
    bottomNavigationOptions: { enabled: false, showOnMobile: true, showOnDesktop: false },
    uiPreferences: {},
    uiPreferencesEndpoint: '',
    ...overrides.config
  }
  return createStore({
    modules: { config: { ...configModule, state: configState } }
  })
}

const TestComponent = defineComponent({
  setup() {
    return useNavigationLayout()
  },
  render: () => h('div')
})

async function factory(store) {
  return mount(TestComponent, {
    global: { plugins: [vuetify, store] }
  })
}

describe('useNavigationLayout', () => {
  test('returns topbarOptions, bottomNavOptions, showTopbar, showBottomNav', async () => {
    const store = createStoreWithConfig()
    const wrapper = await factory(store)
    expect(wrapper.vm.topbarOptions).toBeDefined()
    expect(wrapper.vm.bottomNavOptions).toBeDefined()
    expect(wrapper.vm.showTopbar).toBeDefined()
    expect(wrapper.vm.showBottomNav).toBeDefined()
  })

  test('showTopbar respects enabled flag', async () => {
    const store = createStoreWithConfig({
      config: { topbarOptions: { enabled: false } }
    })
    const wrapper = await factory(store)
    expect(wrapper.vm.showTopbar).toBe(false)
  })

  test('showBottomNav false when bottomNav disabled', async () => {
    const store = createStoreWithConfig({
      config: { bottomNavigationOptions: { enabled: false } }
    })
    const wrapper = await factory(store)
    expect(wrapper.vm.showBottomNav).toBe(false)
  })

  test('persistUiPreferences commits SET_UI_PREFERENCES', async () => {
    const store = createStoreWithConfig({ config: { uiPreferencesEndpoint: '' } })
    const spy = vi.spyOn(store, 'commit')
    const wrapper = await factory(store)
    await wrapper.vm.persistUiPreferences({ sidebar: { rail: true } })
    expect(spy).toHaveBeenCalledWith(expect.any(String), { sidebar: { rail: true } })
  })

  test('persistUiPreferences calls axios when endpoint set', async () => {
    window.axios = { put: vi.fn().mockResolvedValue({ data: { ui_preferences: { sidebar: { rail: true } } } }) }
    const store = createStoreWithConfig({
      config: { uiPreferencesEndpoint: '/api/ui-preferences' }
    })
    const wrapper = await factory(store)
    await wrapper.vm.persistUiPreferences({ sidebar: { rail: true } })
    expect(window.axios.put).toHaveBeenCalledWith('/api/ui-preferences', {
      ui_preferences: { sidebar: { rail: true } }
    })
  })
})
