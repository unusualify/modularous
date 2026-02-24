import { describe, expect, test, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { defineComponent, h } from 'vue'
import { createStore } from 'vuex'
import useSidebar from '../../src/js/hooks/useSidebar'
import { CONFIG } from '../../src/js/store/mutations'
import createModularityVuetify from '../../src/js/plugins/vuetify'

const vuetify = createModularityVuetify()

function createTestStore(initialState = {}) {
  const configState = {
    sidebarStatus: true,
    sidebarOptions: {
      expandHover: 'mini',
      expandOnHover: true,
      rail: false,
      width: 264,
      railWidth: 56,
      hoverZoneWidth: 12,
    },
    secondarySidebarOptions: {},
    topbarOptions: {},
    bottomNavigationOptions: {},
    uiPreferences: {},
    uiPreferencesEndpoint: '',
    profileMenu: [],
    ...initialState,
  }

  const store = createStore({
    modules: {
      config: {
        namespaced: false,
        state: configState,
        getters: {
          sidebarStatus: (s) => s.sidebarStatus,
          sidebarOptions: (s) => s.sidebarOptions,
          uiPreferences: (s) => s.uiPreferences,
        },
        mutations: {
          [CONFIG.SET_SIDEBAR](state, status) {
            state.sidebarStatus = status
          },
          [CONFIG.SET_UI_PREFERENCES](state, preferences) {
            const merged = { ...state.uiPreferences }
            if (preferences.sidebar && typeof preferences.sidebar === 'object') {
              merged.sidebar = { ...(merged.sidebar || {}), ...preferences.sidebar }
            }
            state.uiPreferences = merged
          },
        },
      },
    },
  })

  return store
}

const TestComponent = defineComponent({
  setup() {
    const sidebar = useSidebar()
    return () =>
      h('div', { 'data-testid': 'sidebar' }, [
        h('span', { 'data-testid': 'fullyHidden' }, String(sidebar.fullyHidden?.value ?? sidebar.fullyHidden)),
        h('span', { 'data-testid': 'rail' }, String(sidebar.rail?.value ?? sidebar.rail)),
        h('button', {
          'data-testid': 'resizeStart',
          onClick: (e) => {
            const fn = sidebar.handleResizeStart?.value ?? sidebar.handleResizeStart
            if (typeof fn === 'function') fn(e)
          },
        }, 'Resize'),
        h('div', {
          'data-testid': 'leave',
          onMouseleave: () => {
            const fn = sidebar.handleSidebarLeave?.value ?? sidebar.handleSidebarLeave
            if (typeof fn === 'function') fn()
          },
        }, 'Area'),
      ])
  },
})

describe('useSidebar', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  test('returns expected shape with mini mode defaults', async () => {
    const store = createTestStore()
    const wrapper = mount(TestComponent, {
      global: {
        plugins: [store, vuetify],
      },
    })

    await wrapper.vm.$nextTick()

    expect(wrapper.find('[data-testid="sidebar"]').exists()).toBe(true)
    expect(wrapper.find('[data-testid="fullyHidden"]').text()).toBe('false')
  })

  test('handleResizeStart sets cursor when not in rail mode', async () => {
    const store = createTestStore({
      sidebarOptions: { expandHover: 'mini', rail: false },
      uiPreferences: { sidebar: { rail: false } },
    })
    const wrapper = mount(TestComponent, {
      global: {
        plugins: [store, vuetify],
      },
    })

    await wrapper.vm.$nextTick()

    const resizeBtn = wrapper.find('[data-testid="resizeStart"]')
    resizeBtn.trigger('click')

    expect(document.body.style.cursor).toBe('col-resize')
    document.body.style.cursor = 'default'
    document.body.style.userSelect = 'auto'
  })

  test('handleSidebarLeave closes sidebar when fullyHidden and not pinned', async () => {
    const store = createTestStore({
      sidebarOptions: { expandHover: 'hidden' },
      uiPreferences: { sidebar: { pinned: false } },
    })
    store.commit(CONFIG.SET_SIDEBAR, true)

    const wrapper = mount(TestComponent, {
      global: {
        plugins: [store, vuetify],
      },
    })

    await wrapper.vm.$nextTick()
    expect(store.state.config.sidebarStatus).toBe(true)

    wrapper.find('[data-testid="leave"]').trigger('mouseleave')
    await wrapper.vm.$nextTick()

    expect(store.state.config.sidebarStatus).toBe(false)
  })
})
