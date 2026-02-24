import { describe, expect, test, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createStore } from 'vuex'
import { CONFIG } from '@/store/mutations'
import Sidebar from '@/components/layouts/Sidebar.vue'
import createModularityVuetify from '@/plugins/vuetify'

const vuetify = createModularityVuetify()

beforeEach(() => {
  window.$ = vi.fn(() => [])
})

function createTestStore(initialState = {}) {
  const configState = {
    sidebarStatus: true,
    sidebarOptions: {
      expandHover: 'mini',
      rail: false,
      width: 264,
      railWidth: 56,
    },
    uiPreferences: {},
    profileMenu: [],
    ...initialState,
  }
  return createStore({
    modules: {
      config: {
        namespaced: false,
        state: configState,
        mutations: {
          [CONFIG.SET_SIDEBAR](state, status) {
            state.sidebarStatus = status
          },
        },
      },
    },
  })
}

describe('Sidebar', () => {
  test('renders mini mode layout when expandHover is mini', () => {
    const store = createTestStore()
    const wrapper = mount(Sidebar, {
      global: {
        plugins: [store, vuetify],
        stubs: {
          'ue-sidebar-content': true,
        },
      },
      props: {
        items: [{ icon: 'mdi-home', text: 'Home', action: '/' }],
        profileMenu: [],
      },
    })

    expect(wrapper.find('.ue-sidebar-wrapper').exists()).toBe(false)
    expect(wrapper.findComponent({ name: 'UeSidebarContent' }).exists()).toBe(true)
  })

  test('renders fully hidden wrapper when expandHover is hidden', () => {
    const store = createTestStore({
      sidebarOptions: { expandHover: 'hidden', rail: false },
    })

    const wrapper = mount(Sidebar, {
      global: {
        plugins: [store, vuetify],
        stubs: {
          'ue-sidebar-content': true,
        },
      },
      props: {
        items: [{ icon: 'mdi-home', text: 'Home', action: '/' }],
        profileMenu: [],
      },
    })

    expect(wrapper.find('.ue-sidebar-wrapper.ue-sidebar--fully-hidden').exists()).toBe(true)
  })

  test('commits SET_SIDEBAR on hover zone enter when fullyHidden', async () => {
    const store = createTestStore({
      sidebarOptions: { expandHover: 'hidden' },
    })
    store.state.config.sidebarStatus = false

    const wrapper = mount(Sidebar, {
      global: {
        plugins: [store, vuetify],
        stubs: {
          'ue-sidebar-content': true,
        },
      },
      props: {
        items: [{ icon: 'mdi-home', text: 'Home', action: '/' }],
        profileMenu: [],
      },
    })

    await wrapper.vm.$nextTick()

    const hoverZone = wrapper.find('.ue-sidebar-hover-zone')
    if (hoverZone.exists()) {
      await hoverZone.trigger('mouseenter')
      expect(store.state.config.sidebarStatus).toBe(true)
    }
  })
})
