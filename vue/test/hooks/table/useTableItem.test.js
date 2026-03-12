import { describe, expect, test, vi } from 'vitest'
import { createStore } from 'vuex'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import useTableItem from '../../../src/js/hooks/table/useTableItem.js'

const getModel = vi.fn(() => ({ name: '', email: '' }))
vi.mock('@/utils/getFormData.js', () => ({
  getModel: (...args) => getModel(...args)
}))

const TestComponent = defineComponent({
  props: {
    modelValue: { default: null },
    formSchema: { type: Object, default: () => ({}) }
  },
  setup(props, context) {
    return useTableItem(props, context)
  },
  render: () => h('div')
})

function createStoreStub() {
  return createStore({ state: {}, getters: {}, mutations: {} })
}

async function factory(props = {}) {
  return mount(TestComponent, {
    global: { plugins: [createStoreStub()] },
    props: {
      formSchema: { name: { name: 'name' }, email: { name: 'email' } },
      ...props
    }
  })
}

describe('useTableItem', () => {
  test('returns editedItem, isSoftDeletableItem, itemIsDeleted', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.editedItem).toBeDefined()
    expect(wrapper.vm.isSoftDeletableItem).toBeDefined()
    expect(wrapper.vm.itemIsDeleted).toBeDefined()
  })

  test('setEditedItem updates editedItem', async () => {
    const wrapper = await factory()
    wrapper.vm.setEditedItem({ id: 1, name: 'John' })
    expect(wrapper.vm.editedItem).toEqual({ id: 1, name: 'John' })
  })

  test('isSoftDeletable returns true when item has deleted_at', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.isSoftDeletable({ deleted_at: '2024-01-01' })).toBe(true)
    expect(wrapper.vm.isSoftDeletable({})).toBe(false)
  })

  test('isDeleted returns true when item has deleted_at', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.isDeleted({ deleted_at: '2024-01-01' })).toBe(true)
    expect(wrapper.vm.isDeleted({})).toBe(false)
  })

  test('resetEditedItem resets to getModel from schema', async () => {
    const wrapper = await factory()
    getModel.mockReturnValue({ name: '', email: '' })
    wrapper.vm.setEditedItem({ id: 1, name: 'John' })
    wrapper.vm.resetEditedItem()
    await wrapper.vm.$nextTick()
    expect(getModel).toHaveBeenCalledWith(wrapper.props('formSchema'))
  })
})
