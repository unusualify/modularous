import { describe, expect, test, vi } from 'vitest'
import { createStore } from 'vuex'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import i18n from '../../../src/js/config/i18n'
import useTableIterator from '../../../src/js/hooks/table/useTableIterator.js'

const vuetify = createVuetify({ components, directives })

const mockItemHasAction = vi.fn(() => true)
vi.mock('@/hooks/table/useTableItemActions.js', () => ({
  default: () => ({ itemHasAction: mockItemHasAction })
}))

const mockUseTableNames = vi.fn(() => ({
  permissionName: { value: 'posts' },
  transNamePlural: { value: 'Posts' },
  tableTranslationNotation: { value: 'modules.posts.name' }
}))
vi.mock('@/hooks/table/useTableNames.js', () => ({
  default: (props, ctx) => mockUseTableNames(props, ctx)
}))

const mockUseTableForms = vi.fn(() => ({}))
vi.mock('@/hooks/table/useTableForms.js', () => ({
  default: (props, ctx) => mockUseTableForms(props, ctx)
}))

function createStoreStub() {
  return createStore({
    state: {},
    getters: { totalElements: () => 0 }
  })
}

const TestComponent = defineComponent({
  props: {
    name: { type: String, default: 'posts' },
    headers: { type: Object, default: () => ({}) },
    formSchema: { type: Object, default: () => ({}) }
  },
  emits: ['click-action', 'edit-item'],
  setup(props, context) {
    return useTableIterator(props, context)
  },
  template: '<div />'
})

async function factory(props = {}) {
  return mount(TestComponent, {
    global: {
      plugins: [vuetify, i18n, createStoreStub()]
    },
    props: {
      name: 'posts',
      headers: { name: { key: 'name', title: 'Name' } },
      formSchema: {},
      ...props
    }
  })
}

describe('useTableIterator', () => {
  test('returns headersWithKeys, canItemAction, isSoftDeletable, itemAction, editItem, itemHasAction', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.headersWithKeys).toBeDefined()
    expect(typeof wrapper.vm.canItemAction).toBe('function')
    expect(typeof wrapper.vm.isSoftDeletable).toBe('function')
    expect(typeof wrapper.vm.itemAction).toBe('function')
    expect(typeof wrapper.vm.editItem).toBe('function')
    expect(typeof wrapper.vm.itemHasAction).toBe('function')
  })

  test('headersWithKeys maps headers by key', async () => {
    const wrapper = await factory({
      headers: { col1: { key: 'name', title: 'Name' } }
    })
    expect(wrapper.vm.headersWithKeys).toHaveProperty('name')
    expect(wrapper.vm.headersWithKeys.name).toEqual({ key: 'name', title: 'Name' })
  })

  test('itemAction emits click-action', async () => {
    const wrapper = await factory()
    wrapper.vm.itemAction({ id: 1 }, { name: 'edit' })
    expect(wrapper.emitted('click-action')).toEqual([[{ id: 1 }, { name: 'edit' }]])
  })

  test('editItem emits edit-item', async () => {
    const wrapper = await factory()
    wrapper.vm.editItem({ id: 1 })
    expect(wrapper.emitted('edit-item')).toEqual([[{ id: 1 }]])
  })

  test('isSoftDeletable returns true for deleted item', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.isSoftDeletable({ deleted_at: '2024-01-01' })).toBe(true)
    expect(wrapper.vm.isSoftDeletable({})).toBe(false)
  })
})
