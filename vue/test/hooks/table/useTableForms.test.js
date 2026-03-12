import { describe, expect, test, vi } from 'vitest'
import { createStore } from 'vuex'
import { defineComponent, ref } from 'vue'
import { mount } from '@vue/test-utils'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import i18n from '../../../src/js/config/i18n'
import useTableForms, { makeTableFormsProps } from '../../../src/js/hooks/table/useTableForms.js'

const vuetify = createVuetify({ components, directives })

function createStoreStub(overrides = {}) {
  return createStore({
    state: {
      form: { loading: false, errors: {} },
      ...overrides.state
    }
  })
}

const TestComponent = defineComponent({
  props: {
    formSchema: { type: Object, required: true },
    ...makeTableFormsProps()
  },
  setup(props, context) {
    const editedItem = ref({})
    const setEditedItem = vi.fn()
    const resetEditedItem = vi.fn()
    const transNameSingular = ref('Item')
    const loadItems = vi.fn()
    const state = { id: 'test-table-123' }
    const ctx = {
      ...context,
      TableItem: { editedItem, setEditedItem, resetEditedItem },
      transNameSingular,
      loadItems,
      state
    }
    return useTableForms(props, ctx)
  },
  template: '<div />'
})

async function factory(store, props = {}) {
  return mount(TestComponent, {
    global: { plugins: [vuetify, i18n, store] },
    props: { formSchema: { name: { name: 'name' } }, ...props }
  })
}

describe('useTableForms', () => {
  test('returns formRef, formStyles, openForm, closeForm, createForm', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    expect(wrapper.vm.formRef).toBeDefined()
    expect(wrapper.vm.formStyles).toBeDefined()
    expect(typeof wrapper.vm.openForm).toBe('function')
    expect(typeof wrapper.vm.closeForm).toBe('function')
    expect(typeof wrapper.vm.createForm).toBe('function')
  })

  test('formRef uses state.id when provided', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    expect(wrapper.vm.formRef).toBe('test-table-123-form')
  })

  test('openForm sets formActive true', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    wrapper.vm.openForm()
    expect(wrapper.vm.formActive).toBe(true)
  })

  test('closeForm sets formActive false', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    wrapper.vm.openForm()
    wrapper.vm.closeForm()
    expect(wrapper.vm.formActive).toBe(false)
  })

  test('createForm resets editedItem and opens form', async () => {
    const store = createStoreStub()
    const resetEditedItem = vi.fn()
    const TestWithSpy = defineComponent({
      props: { formSchema: { type: Object, required: true }, ...makeTableFormsProps() },
      setup(props, context) {
        const editedItem = ref({})
        const transNameSingular = ref('Item')
        const loadItems = vi.fn()
        const state = { id: 'test' }
        const ctx = {
          ...context,
          TableItem: { editedItem, setEditedItem: vi.fn(), resetEditedItem },
          transNameSingular,
          loadItems,
          state
        }
        return useTableForms(props, ctx)
      },
      template: '<div />'
    })
    const wrapper = mount(TestWithSpy, {
      global: { plugins: [vuetify, i18n, store] },
      props: { formSchema: {} }
    })
    wrapper.vm.createForm()
    expect(resetEditedItem).toHaveBeenCalled()
    expect(wrapper.vm.formActive).toBe(true)
  })

  test('handleFormSubmission with success closes form and loads items', async () => {
    const store = createStoreStub()
    const loadItems = vi.fn()
    const TestWithSpy = defineComponent({
      props: { formSchema: { type: Object, required: true }, ...makeTableFormsProps() },
      setup(props, context) {
        const editedItem = ref({})
        const ctx = {
          ...context,
          TableItem: { editedItem, setEditedItem: vi.fn(), resetEditedItem: vi.fn() },
          transNameSingular: ref('Item'),
          loadItems,
          state: { id: 'test' }
        }
        return useTableForms(props, ctx)
      },
      template: '<div />'
    })
    const wrapper = mount(TestWithSpy, {
      global: { plugins: [vuetify, i18n, store] },
      props: { formSchema: {} }
    })
    wrapper.vm.openForm()
    wrapper.vm.handleFormSubmission({ variant: 'success' })
    expect(wrapper.vm.formActive).toBe(false)
    expect(loadItems).toHaveBeenCalled()
  })

  test('formStyles returns width from formWidth prop', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, { formWidth: '80%' })
    expect(wrapper.vm.formStyles).toEqual({ width: '80%' })
  })
})
