import { describe, expect, test } from 'vitest'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import useActiveTableItem from '../../src/js/hooks/useActiveTableItem.js'

const vuetify = createVuetify({ components, directives })

const TestComponent = defineComponent({
  props: {
    modelValue: { default: null },
    itemData: { type: Object, default: () => ({}) }
  },
  emits: ['update:modelValue', 'toggle'],
  setup(props, context) {
    return useActiveTableItem(props, context)
  },
  template: '<div />'
})

async function factory(props = {}) {
  return mount(TestComponent, {
    global: { plugins: [vuetify] },
    props: { modelValue: null, itemData: {}, ...props }
  })
}

describe('useActiveTableItem', () => {
  test('returns item, modalStatus, activeKey, activeBlock, items', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.item).toBeDefined()
    expect(wrapper.vm.modalStatus).toBeDefined()
    expect(wrapper.vm.activeKey).toBeNull()
    expect(wrapper.vm.activeBlock).toBeNull()
    expect(wrapper.vm.items).toEqual([])
  })

  test('selectNested sets activeKey and emits toggle', async () => {
    const wrapper = await factory({ itemData: { block1: { title: 'Block 1' } } })
    wrapper.vm.selectNested('block1')
    expect(wrapper.vm.activeKey).toBe('block1')
    expect(wrapper.vm.activeBlock).toEqual({ title: 'Block 1' })
    expect(wrapper.emitted('toggle')).toEqual([[true]])
  })

  test('clickOutside clears item and activeKey', async () => {
    const wrapper = await factory({ modelValue: { id: 1 } })
    await wrapper.vm.$nextTick()
    wrapper.vm.selectNested('block1')
    wrapper.vm.clickOutside()
    expect(wrapper.vm.item).toBeNull()
    expect(wrapper.vm.activeKey).toBeNull()
  })

  test('closeItemDetails clears activeKey and emits toggle', async () => {
    const wrapper = await factory()
    wrapper.vm.selectNested('block1')
    wrapper.vm.closeItemDetails()
    expect(wrapper.vm.activeKey).toBeNull()
    expect(wrapper.emitted('toggle')).toContainEqual([false])
  })
})
