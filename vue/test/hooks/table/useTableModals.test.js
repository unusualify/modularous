import { describe, expect, test } from 'vitest'
import { createStore } from 'vuex'
import { defineComponent, ref } from 'vue'
import { mount } from '@vue/test-utils'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import i18n from '../../../src/js/config/i18n'
import useTableModals, { makeTableModalsProps } from '../../../src/js/hooks/table/useTableModals.js'

const vuetify = createVuetify({ components, directives })

function createStoreStub() {
  const store = createStore({
    state: {},
    getters: {}
  })
  store._state.data = {
    datatable: {
      customModal: {
        description: '',
        icon: ''
      }
    }
  }
  return store
}

const TestComponent = defineComponent({
  props: {
    ...makeTableModalsProps()
  },
  setup(props, context) {
    const editedItem = ref({})
    const transNameSingular = ref('Post')
    const ctx = {
      ...context,
      TableItem: { editedItem },
      TableNames: { transNameSingular }
    }
    return useTableModals(props, ctx)
  },
  template: '<div />'
})

async function factory(store, props = {}) {
  return mount(TestComponent, {
    global: { plugins: [vuetify, i18n, store] },
    props: { ...props }
  })
}

describe('useTableModals', () => {
  test('returns deleteModalActive, customModalActive, openCustomModal, closeCustomModal', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    expect(wrapper.vm.deleteModalActive).toBeDefined()
    expect(wrapper.vm.customModalActive).toBeDefined()
    expect(typeof wrapper.vm.openCustomModal).toBe('function')
    expect(typeof wrapper.vm.closeCustomModal).toBe('function')
  })

  test('openCustomModal sets customModalActive true', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    wrapper.vm.openCustomModal()
    expect(wrapper.vm.customModalActive).toBe(true)
  })

  test('closeCustomModal sets customModalActive false', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, { openCustomModal: true })
    wrapper.vm.closeCustomModal()
    expect(wrapper.vm.customModalActive).toBe(false)
  })

  test('setModalType runs without error', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    expect(() => wrapper.vm.setModalType('dialog')).not.toThrow()
  })

  test('makeTableModalsProps defines openCustomModal', () => {
    const props = makeTableModalsProps()
    expect(props.openCustomModal.default).toBe(false)
  })
})
