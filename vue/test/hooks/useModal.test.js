import { describe, expect, test } from 'vitest'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import useModal from '../../src/js/hooks/useModal.js'

const vuetify = createVuetify({ components, directives })

const TestComponent = defineComponent({
  props: {
    modelValue: { type: Boolean, default: false },
    useModelValue: { type: Boolean, default: true },
    fullscreen: { type: Boolean, default: false },
    widthType: { type: String, default: 'md' }
  },
  emits: ['update:modelValue', 'opened', 'click:outside'],
  setup(props, context) {
    return useModal(props, context)
  },
  template: '<div />'
})

// useModelValue: false uses internal state instead of prop, so open/close work without parent sync
const UncontrolledModal = defineComponent({
  props: {
    useModelValue: { type: Boolean, default: false },
    fullscreen: { type: Boolean, default: false },
    widthType: { type: String, default: 'md' }
  },
  emits: ['update:modelValue', 'opened', 'click:outside'],
  setup(props, context) {
    return useModal(props, context)
  },
  template: '<div />'
})

async function factory(props = {}) {
  return mount(TestComponent, {
    global: { plugins: [vuetify] },
    props: { modelValue: false, ...props }
  })
}

describe('useModal', () => {
  test('returns dialog, openModal, closeModal, toggleModal', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.dialog).toBe(false)
    expect(typeof wrapper.vm.openModal).toBe('function')
    expect(typeof wrapper.vm.closeModal).toBe('function')
    expect(typeof wrapper.vm.toggleModal).toBe('function')
  })

  test('openModal sets dialog to true when useModelValue is false', async () => {
    const wrapper = mount(UncontrolledModal, {
      global: { plugins: [vuetify] },
      props: { useModelValue: false }
    })
    wrapper.vm.openModal()
    expect(wrapper.vm.dialog).toBe(true)
  })

  test('closeModal sets dialog to false when useModelValue is false', async () => {
    const wrapper = mount(UncontrolledModal, {
      global: { plugins: [vuetify] },
      props: { useModelValue: false }
    })
    wrapper.vm.openModal()
    expect(wrapper.vm.dialog).toBe(true)
    wrapper.vm.closeModal()
    expect(wrapper.vm.dialog).toBe(false)
  })

  test('toggleModal toggles dialog when useModelValue is false', async () => {
    const wrapper = mount(UncontrolledModal, {
      global: { plugins: [vuetify] },
      props: { useModelValue: false }
    })
    wrapper.vm.toggleModal()
    expect(wrapper.vm.dialog).toBe(true)
    wrapper.vm.toggleModal()
    expect(wrapper.vm.dialog).toBe(false)
  })

  test('modalWidth returns default width when not fullscreen', async () => {
    const wrapper = await factory({ fullscreen: false, widthType: 'md' })
    expect(wrapper.vm.modalWidth).toBe('720px')
  })
})
