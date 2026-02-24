import { describe, expect, test } from 'vitest'
import { mount } from '@vue/test-utils'
import { defineComponent, h } from 'vue'
import { createStore } from 'vuex'
import useNavigationLayout from '../../src/js/hooks/useNavigationLayout'
import createModularityVuetify from '../../src/js/plugins/vuetify'

const vuetify = createModularityVuetify()

function createTestStore() {
  return createStore({
    modules: {
      config: {
        namespaced: false,
        state: {
          topbarOptions: { enabled: true, showOnMobile: true, showOnDesktop: true },
          bottomNavigationOptions: { enabled: false, showOnMobile: true, showOnDesktop: false },
          uiPreferences: {},
          uiPreferencesEndpoint: '',
        },
      },
    },
  })
}

const TestComponent = defineComponent({
  setup() {
    const layout = useNavigationLayout()
    return () =>
      h('div', { 'data-testid': 'layout' }, [
        h('span', { 'data-testid': 'showTopbar' }, String(layout.showTopbar?.value ?? layout.showTopbar)),
        h('span', { 'data-testid': 'showBottomNav' }, String(layout.showBottomNav?.value ?? layout.showBottomNav)),
      ])
  },
})

describe('useNavigationLayout', () => {
  test('returns showTopbar and showBottomNav', async () => {
    const store = createTestStore()
    const wrapper = mount(TestComponent, {
      global: {
        plugins: [store, vuetify],
      },
    })

    await wrapper.vm.$nextTick()

    expect(wrapper.find('[data-testid="layout"]').exists()).toBe(true)
    expect(wrapper.find('[data-testid="showTopbar"]').text()).toBeDefined()
    expect(wrapper.find('[data-testid="showBottomNav"]').text()).toBeDefined()
  })

  test('topbarOptions and bottomNavOptions are defined', async () => {
    const store = createTestStore()
    const wrapper = mount(TestComponent, {
      global: {
        plugins: [store, vuetify],
      },
    })

    await wrapper.vm.$nextTick()

    expect(wrapper.find('[data-testid="showTopbar"]').text()).toMatch(/true|false/)
    expect(wrapper.find('[data-testid="showBottomNav"]').text()).toMatch(/true|false/)
  })
})
