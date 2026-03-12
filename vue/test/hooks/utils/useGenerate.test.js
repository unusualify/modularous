import { describe, expect, test, vi } from 'vitest'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import { createStore } from 'vuex'
import useGenerate from '../../../src/js/hooks/utils/useGenerate.js'
import configModule from '../../../src/js/store/modules/config.js'
import ambientModule from '../../../src/js/store/modules/ambient.js'

const router = { visit: vi.fn() }
vi.mock('@inertiajs/vue3', () => ({ router: { visit: vi.fn() } }))

const vuetify = createVuetify({ components, directives })

function createStoreWithConfig(overrides = {}) {
  const configState = { ...configModule.state, isInertia: false, ...overrides.config }
  const ambientState = { ...ambientModule.state, isHot: false, ...overrides.ambient }
  return createStore({
    modules: {
      config: { ...configModule, state: configState },
      ambient: { ...ambientModule, state: ambientState }
    }
  })
}

const TestComponent = defineComponent({
  props: { icon: String, href: String, label: String, forceLabel: Boolean },
  setup(props, context) {
    return useGenerate(props, context)
  },
  render: () => h('div')
})

async function factory(store, props = {}) {
  return mount(TestComponent, {
    global: { plugins: [vuetify, store] },
    props: { label: 'Action', ...props }
  })
}

describe('useGenerate', () => {
  test('returns generateButtonProps and generatedButtonProps', async () => {
    const store = createStoreWithConfig()
    const wrapper = await factory(store)
    expect(typeof wrapper.vm.generateButtonProps).toBe('function')
    expect(wrapper.vm.generatedButtonProps).toBeDefined()
  })

  test('generateButtonProps returns button config for action', async () => {
    const store = createStoreWithConfig()
    const wrapper = await factory(store)
    const result = wrapper.vm.generateButtonProps({
      label: 'Save',
      icon: 'mdi-save',
      color: 'primary'
    })
    expect(result.text).toBeNull()
    expect(result.icon).toBe('mdi-save')
    expect(result.color).toBe('primary')
    expect(result.disabled).toBe(false)
  })

  test('generateButtonProps with forceLabel sets text not icon', async () => {
    const store = createStoreWithConfig()
    const wrapper = await factory(store)
    const result = wrapper.vm.generateButtonProps({
      label: 'Save',
      icon: 'mdi-save',
      forceLabel: true
    })
    expect(result.text).toBe('Save')
    expect(result.icon).toBeNull()
  })

  test('generateButtonProps with href adds onClick that opens window when target _blank', async () => {
    const store = createStoreWithConfig()
    const openSpy = vi.spyOn(window, 'open').mockImplementation(() => {})
    const wrapper = await factory(store)
    const result = wrapper.vm.generateButtonProps({
      href: 'https://example.com',
      target: '_blank'
    })
    expect(result.onClick).toBeDefined()
    const e = { preventDefault: vi.fn() }
    result.onClick(e)
    expect(e.preventDefault).toHaveBeenCalled()
    expect(openSpy).toHaveBeenCalledWith('https://example.com', '_blank')
    openSpy.mockRestore()
  })

  test('generatedButtonProps returns empty when props null', async () => {
    const store = createStoreWithConfig()
    const NullProps = defineComponent({
      setup(_, ctx) {
        return useGenerate(null, ctx)
      },
      render: () => h('div')
    })
    const wrapper = mount(NullProps, {
      global: { plugins: [vuetify, store] }
    })
    expect(wrapper.vm.generatedButtonProps).toEqual({})
  })
})
