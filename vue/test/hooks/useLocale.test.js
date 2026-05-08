import { describe, expect, test, vi } from 'vitest'
import { createStore } from 'vuex'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import useLocale from '../../src/js/hooks/useLocale.js'
import languageModule from '../../src/js/store/modules/language.js'
import { LANGUAGE } from '../../src/js/store/mutations/index.js'

function createStoreWithLanguage(overrides = {}) {
  const languageState = {
    all: [
      { value: 'en', shortlabel: 'en' },
      { value: 'ar', shortlabel: 'ar' }
    ],
    active: { value: 'en', shortlabel: 'en' },
    ...overrides.language
  }
  return createStore({
    modules: {
      language: { ...languageModule, state: languageState }
    }
  })
}

const TestComponent = defineComponent({
  setup() {
    return useLocale()
  },
  render: () => h('div')
})

async function factory(store) {
  return mount(TestComponent, {
    global: { plugins: [store] }
  })
}

describe('useLocale', () => {
  test('returns currentLocale from store', async () => {
    const store = createStoreWithLanguage()
    const wrapper = await factory(store)
    expect(wrapper.vm.currentLocale).toEqual({ value: 'en', shortlabel: 'en' })
  })

  test('returns languages from store', async () => {
    const store = createStoreWithLanguage()
    const wrapper = await factory(store)
    expect(wrapper.vm.languages).toHaveLength(2)
  })

  test('hasCurrentLocale is true when active exists', async () => {
    const store = createStoreWithLanguage()
    const wrapper = await factory(store)
    expect(wrapper.vm.hasCurrentLocale).toBe(true)
  })

  test('isLocaleRTL returns true for RTL locale', async () => {
    const store = createStoreWithLanguage({
      language: {
        all: [{ value: 'ar', shortlabel: 'ar' }],
        active: { value: 'ar', shortlabel: 'ar' }
      }
    })
    const RTLComponent = defineComponent({
      setup() {
        return useLocale({ locale: { shortlabel: 'ar' } })
      },
      render: () => h('div')
    })
    const wrapper = mount(RTLComponent, {
      global: { plugins: [store] }
    })
    expect(wrapper.vm.isLocaleRTL).toBe(true)
    expect(wrapper.vm.dirLocale).toBe('rtl')
  })

  test('updateLocale commits UPDATE_LANG', async () => {
    const store = createStoreWithLanguage()
    const spy = vi.spyOn(store, 'commit')
    const wrapper = await factory(store)
    wrapper.vm.updateLocale('en')
    expect(spy).toHaveBeenCalledWith(LANGUAGE.UPDATE_LANG, 'en')
  })
})
