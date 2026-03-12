import { describe, expect, test } from 'vitest'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import useModelValue from '../../src/js/hooks/useModelValue.js'

const TestComponent = defineComponent({
  props: {
    modelValue: { type: [String, Number, Object, Boolean], default: null }
  },
  emits: ['update:modelValue'],
  setup(props, context) {
    const state = useModelValue(props, context, 'activeItem')
    return state
  },
  template: '<div @click="activeItem = \'new\'">{{ activeItem }}</div>'
})

async function factory(props = {}) {
  return mount(TestComponent, {
    props: { modelValue: 'initial', ...props }
  })
}

describe('useModelValue', () => {
  test('returns activeItem computed from modelValue', async () => {
    const wrapper = await factory({ modelValue: 'foo' })
    expect(wrapper.vm.activeItem).toBe('foo')
  })

  test('emits update:modelValue when activeItem is set', async () => {
    const wrapper = await factory({ modelValue: 'initial' })
    wrapper.vm.activeItem = 'updated'
    await wrapper.vm.$nextTick()
    expect(wrapper.emitted('update:modelValue')).toEqual([['updated']])
  })

  test('supports custom name parameter', async () => {
    const CustomComponent = defineComponent({
      props: { modelValue: { default: 42 } },
      emits: ['update:modelValue'],
      setup(props, ctx) {
        return useModelValue(props, ctx, 'selected')
      },
      template: '<div />'
    })
    const wrapper = mount(CustomComponent, { props: { modelValue: 42 } })
    expect(wrapper.vm.selected).toBe(42)
    wrapper.vm.selected = 100
    await wrapper.vm.$nextTick()
    expect(wrapper.emitted('update:modelValue')).toEqual([[100]])
  })
})
