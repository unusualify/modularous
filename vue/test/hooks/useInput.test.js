import { describe, expect, test } from 'vitest'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import useInput, { makeInputProps, makeInputEmits } from '../../src/js/hooks/useInput.js'

const TestComponent = defineComponent({
  props: {
    modelValue: { default: null },
    obj: { type: Object, default: () => ({}) },
    ...makeInputProps()
  },
  emits: makeInputEmits,
  setup(props, context) {
    return useInput(props, context)
  },
  template: '<div />'
})

async function factory(props = {}) {
  return mount(TestComponent, {
    props: {
      modelValue: 'test',
      obj: { schema: { name: 'field' } },
      ...props
    }
  })
}

describe('useInput', () => {
  test('returns id, input, initialValue, boundProps', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.id).toBeDefined()
    expect(wrapper.vm.id).toMatch(/\d+-input/)
    expect(wrapper.vm.input).toBeDefined()
    expect(wrapper.vm.initialValue).toBeDefined()
  })

  test('input getter returns modelValue when set', async () => {
    const wrapper = await factory({ modelValue: 'hello' })
    expect(wrapper.vm.input).toBe('hello')
  })

  test('updateModelValue emits update:modelValue', async () => {
    const wrapper = await factory()
    wrapper.vm.updateModelValue('new value')
    expect(wrapper.emitted('update:modelValue')[0]).toContain('new value')
  })

  test('makeReference returns key with id suffix', async () => {
    const wrapper = await factory()
    const ref = wrapper.vm.makeReference('label')
    expect(ref).toMatch(/label-\d+-input/)
  })

  test('getReference delegates to makeReference', async () => {
    const wrapper = await factory()
    const ref = wrapper.vm.getReference('hint')
    expect(ref).toMatch(/hint-\d+-input/)
  })

  test('makeInputProps defines modelValue, obj, label', () => {
    const props = makeInputProps()
    expect(props.modelValue).toBeDefined()
    expect(props.obj.default()).toEqual({})
    expect(props.label.default).toBe('')
  })
})
