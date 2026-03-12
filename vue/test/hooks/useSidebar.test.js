import { describe, expect, test, vi } from 'vitest'
import { createStore } from 'vuex'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import useSidebar from '../../src/js/hooks/useSidebar.js'
import configModule from '../../src/js/store/modules/config.js'

const vuetify = createVuetify({ components, directives })

function createStoreWithConfig(overrides = {}) {
  const configState = {
    ...configModule.state,
    sidebarStatus: true,
    sidebarOptions: { width: 264, railWidth: 56, expandHover: 'mini' },
    secondarySidebarOptions: [],
    uiPreferences: {},
    uiPreferencesEndpoint: '',
    profileMenu: [],
    ...overrides.config
  }
  return createStore({
    modules: { config: { ...configModule, state: configState } }
  })
}

const TestComponent = defineComponent({
  setup() {
    return useSidebar()
  },
  render: () => h('div')
})

async function factory(store) {
  return mount(TestComponent, {
    global: { plugins: [vuetify, store] }
  })
}

describe('useSidebar', () => {
  test('returns status, options, handleRailToggle, handleResizeStart', async () => {
    const store = createStoreWithConfig()
    const wrapper = await factory(store)
    expect(wrapper.vm.status).toBeDefined()
    expect(wrapper.vm.options).toBeDefined()
    expect(typeof wrapper.vm.handleRailToggle).toBe('function')
    expect(typeof wrapper.vm.handleResizeStart).toBe('function')
  })

  test('status reflects store sidebarStatus', async () => {
    const store = createStoreWithConfig({ config: { sidebarStatus: false } })
    const wrapper = await factory(store)
    expect(wrapper.vm.status).toBe(false)
  })

  test('handleRailToggle commits and persists', async () => {
    const store = createStoreWithConfig()
    const commitSpy = vi.spyOn(store, 'commit')
    const wrapper = await factory(store)
    wrapper.vm.handleRailToggle()
    expect(commitSpy).toHaveBeenCalled()
  })

  test('handleResizeStart sets isResizing and cursor', async () => {
    const store = createStoreWithConfig()
    const wrapper = await factory(store)
    wrapper.vm.handleResizeStart({ preventDefault: vi.fn() })
    expect(wrapper.vm.isResizing).toBe(true)
    expect(document.body.style.cursor).toBe('col-resize')
  })

  test('handleResizeEnd resets isResizing', async () => {
    const store = createStoreWithConfig()
    const wrapper = await factory(store)
    wrapper.vm.handleResizeStart({ preventDefault: vi.fn() })
    wrapper.vm.handleResizeEnd()
    expect(wrapper.vm.isResizing).toBe(false)
    expect(document.body.style.cursor).toBe('default')
  })
})
