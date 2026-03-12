import { describe, expect, test } from 'vitest'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import useInputHandlers from '../../src/js/hooks/useInputHandlers.js'

const TestComponent = defineComponent({
  setup() {
    return useInputHandlers()
  },
  render: () => h('div')
})

async function factory() {
  return mount(TestComponent)
}

describe('useInputHandlers', () => {
  test('returns invokeInputClickHandler and passwordHandler', async () => {
    const wrapper = await factory()
    expect(typeof wrapper.vm.invokeInputClickHandler).toBe('function')
    expect(typeof wrapper.vm.passwordHandler).toBe('function')
  })

  test('passwordHandler toggles type between password and text', async () => {
    const wrapper = await factory()
    const obj = { schema: { type: 'password' } }
    wrapper.vm.passwordHandler(obj, 'appendInner')
    expect(obj.schema.type).toBe('text')
    expect(obj.schema.appendInnerIcon).toBe('$visibility')
    wrapper.vm.passwordHandler(obj, 'appendInner')
    expect(obj.schema.type).toBe('password')
    expect(obj.schema.appendInnerIcon).toBe('$non-visibility')
  })

  test('invokeInputClickHandler calls handler when slotHandlers defined', async () => {
    const wrapper = await factory()
    const obj = {
      schema: {
        type: 'password',
        slotHandlers: { appendInner: 'password' }
      }
    }
    wrapper.vm.invokeInputClickHandler(obj, 'append-inner')
    expect(obj.schema.type).toBe('text')
  })

  test('invokeInputClickHandler does nothing when no slotHandlers', async () => {
    const wrapper = await factory()
    const obj = { schema: { type: 'password' } }
    wrapper.vm.invokeInputClickHandler(obj, 'append-inner')
    expect(obj.schema.type).toBe('password')
  })
})
