import { describe, expect, test, vi } from 'vitest'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import { createStore } from 'vuex'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import useCurrencyNumber from '../../src/js/hooks/useCurrencyNumber.js'
import languageModule from '../../src/js/store/modules/language.js'

const mockInputRef = { value: null }
const mockFormattedValue = { value: '0,00' }
const mockNumberValue = { value: 0 }
const mockSetValue = vi.fn()

vi.mock('vue-currency-input', () => ({
  useCurrencyInput: () => ({
    inputRef: mockInputRef,
    formattedValue: mockFormattedValue,
    numberValue: mockNumberValue,
    setValue: mockSetValue
  })
}))

const vuetify = createVuetify({ components, directives })

function createStoreWithLanguage() {
  const languageState = {
    ...languageModule.state,
    active: { value: 'de' }
  }
  return createStore({
    modules: { language: { ...languageModule, state: languageState } }
  })
}

const TestComponent = defineComponent({
  props: { modelValue: { default: null } },
  setup(props, context) {
    return useCurrencyNumber(props, context)
  },
  template: '<div />'
})

async function factory(store, props = {}) {
  return mount(TestComponent, {
    global: { plugins: [vuetify, store] },
    props: { modelValue: 0, ...props }
  })
}

describe('useCurrencyNumber', () => {
  test('returns inputRef, formattedValue, numberValue', async () => {
    const store = createStoreWithLanguage()
    const wrapper = await factory(store)
    expect(wrapper.vm.inputRef).toBeDefined()
    expect(wrapper.vm.formattedValue).toBeDefined()
    expect(wrapper.vm.numberValue).toBeDefined()
  })

  test('watch triggers setValue when modelValue changes', async () => {
    mockSetValue.mockClear()
    const store = createStoreWithLanguage()
    const wrapper = await factory(store, { modelValue: 0 })
    await wrapper.setProps({ modelValue: 99.99 })
    await new Promise(r => setTimeout(r, 0))
    expect(mockSetValue).toHaveBeenCalledWith(99.99)
  })
})
