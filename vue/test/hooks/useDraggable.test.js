import { describe, expect, test } from 'vitest'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import useDraggable, { makeDraggableProps } from '../../src/js/hooks/useDraggable.js'

const TestComponent = defineComponent({
  props: {
    draggable: { type: Boolean, default: false },
    orderKey: { type: String, default: 'position' },
    ...makeDraggableProps()
  },
  setup(props, context) {
    return useDraggable(props, context)
  },
  template: '<div />'
})

async function factory(props = {}) {
  return mount(TestComponent, {
    props: { draggable: false, ...props }
  })
}

describe('useDraggable', () => {
  test('returns animation, handle, ghostClass, chosenClass, dragClass, dragOptions', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.animation).toBe(150)
    expect(wrapper.vm.handle).toBe('.drag__handle')
    expect(wrapper.vm.ghostClass).toBe('sortable-ghost')
    expect(wrapper.vm.chosenClass).toBe('sortable-chosen')
    expect(wrapper.vm.dragClass).toBe('sortable-drag')
  })

  test('dragOptions disabled when draggable is false', async () => {
    const wrapper = await factory({ draggable: false })
    expect(wrapper.vm.dragOptions.disabled).toBe(true)
  })

  test('dragOptions enabled when draggable is true', async () => {
    const wrapper = await factory({ draggable: true })
    expect(wrapper.vm.dragOptions.disabled).toBe(false)
  })

  test('makeDraggableProps defines draggable and orderKey', () => {
    const props = makeDraggableProps()
    expect(props.draggable.default).toBe(false)
    expect(props.orderKey.default).toBe('position')
  })
})
