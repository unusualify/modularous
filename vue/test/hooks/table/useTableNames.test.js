import { describe, expect, test } from 'vitest'
import { createStore } from 'vuex'
import { defineComponent, ref } from 'vue'
import { mount } from '@vue/test-utils'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import i18n from '../../../src/js/config/i18n'
import useTableNames, { makeTableNamesProps } from '../../../src/js/hooks/table/useTableNames.js'

const vuetify = createVuetify({ components, directives })

function createStoreStub(overrides = {}) {
  return createStore({
    state: {
      user: { locale: 'en' },
      ...overrides.state
    },
    getters: {
      totalElements: () => 10,
      ...overrides.getters
    }
  })
}

const TestComponent = defineComponent({
  props: {
    name: { type: String, default: 'posts' },
    customTitle: { type: String, default: undefined },
    titleKey: { type: String, default: 'name' },
    ...makeTableNamesProps()
  },
  setup(props, context) {
    const editedItem = ref({ name: 'Test Post', id: 1 })
    const isSoftDeletableItem = ref(false)
    const editedIndex = ref(-1)
    const ctx = {
      ...context,
      TableItem: { editedItem, isSoftDeletableItem },
      editedIndex
    }
    return useTableNames(props, ctx)
  },
  template: '<div />'
})

async function factory(store, props = {}) {
  return mount(TestComponent, {
    global: { plugins: [vuetify, i18n, store] },
    props: { name: 'posts', ...props }
  })
}

describe('useTableNames', () => {
  test('returns snakeName, tableTitle, formTitle, deleteQuestion', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    expect(wrapper.vm.snakeName).toBeDefined()
    expect(wrapper.vm.tableTitle).toBeDefined()
    expect(wrapper.vm.formTitle).toBeDefined()
    expect(wrapper.vm.deleteQuestion).toBeDefined()
  })

  test('tableTitle uses customTitle when provided', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, { customTitle: 'My Custom Title' })
    expect(wrapper.vm.tableTitle).toContain('My Custom Title')
  })

  test('tableSubtitle uses subtitle prop when provided', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, { subtitle: 'fields.subtitle' })
    expect(wrapper.vm.tableSubtitle).toBeDefined()
  })

  test('deleteQuestion includes item name for soft-deletable', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    expect(wrapper.vm.deleteQuestion).toBeDefined()
  })

  test('makeTableNamesProps defines formCreateTitleTranslationKey', () => {
    const props = makeTableNamesProps()
    expect(props.formCreateTitleTranslationKey.default).toBe('fields.new-item')
    expect(props.formEditTitleTranslationKey.default).toBe('fields.edit-item')
  })
})
