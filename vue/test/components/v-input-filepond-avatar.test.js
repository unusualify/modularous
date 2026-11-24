import { describe, expect, test } from 'vitest'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

import FilepondAvatar from '../../src/js/components/inputs/FilepondAvatar.vue'

const removeFilesCalls = { count: 0 }

const vuetify = createVuetify({ components, directives })

// Stub the inner filepond; tests emit update:modelValue to simulate FilePond.
const FilepondStub = defineComponent({
  name: 'VInputFilepond',
  props: { modelValue: { type: Array, default: () => [] } },
  emits: ['update:modelValue'],
  setup (props, { emit, expose }) {
    expose({ browse: () => {}, removeFiles: () => { removeFilesCalls.count += 1 } })
    return () => h('div', { class: 'filepond-stub' })
  },
})

// Stub the confirm modal; tests emit `confirm` to simulate the user accepting.
const ModalStub = defineComponent({
  name: 'UeModal',
  props: { modelValue: { type: Boolean, default: false } },
  emits: ['update:modelValue', 'confirm'],
  setup () {
    return () => h('div', { class: 'modal-stub' })
  },
})

const globalOptions = {
  plugins: [vuetify],
  stubs: { VInputFilepond: FilepondStub, 'v-input-filepond': FilepondStub, UeModal: ModalStub, 'ue-modal': ModalStub },
  mocks: { $t: (key) => key },
}

const factory = (modelValue, props = {}) => mount(FilepondAvatar, {
  props: { name: 'avatar', modelValue, ...props },
  global: globalOptions,
})

const oldFile = { uuid: 'old', source: '/load/old' }
const newFile = { uuid: 'new', source: '/load/new' }

describe('v-input-filepond-avatar', () => {
  test('does not emit an empty list during FilePond replace', () => {
    const wrapper = factory([oldFile])

    wrapper.findComponent(FilepondStub).vm.$emit('update:modelValue', [])

    expect(wrapper.emitted('update:modelValue')).toBeUndefined()
  })

  test('emits the processed replacement file', () => {
    const wrapper = factory([oldFile])

    wrapper.findComponent(FilepondStub).vm.$emit('update:modelValue', [newFile])

    expect(wrapper.emitted('update:modelValue')?.at(-1)?.[0]).toEqual([newFile])
  })

  test('remove button asks for confirmation before removing', async () => {
    removeFilesCalls.count = 0
    const wrapper = factory([oldFile])
    const modal = wrapper.findComponent(ModalStub)

    expect(modal.props('modelValue')).toBe(false)

    await wrapper.find('.v-input-filepond__remove-icon').trigger('click')

    expect(modal.props('modelValue')).toBe(true)
    expect(wrapper.emitted('update:modelValue')).toBeUndefined()
    expect(removeFilesCalls.count).toBe(0)
  })

  test('confirming the dialog emits an empty list and clears the pond', async () => {
    removeFilesCalls.count = 0
    const wrapper = factory([oldFile])

    await wrapper.find('.v-input-filepond__remove-icon').trigger('click')
    wrapper.findComponent(ModalStub).vm.$emit('confirm')

    expect(wrapper.emitted('update:modelValue')?.at(-1)?.[0]).toEqual([])
    expect(removeFilesCalls.count).toBe(1)
  })

  test('cancelling the dialog keeps the avatar', async () => {
    const wrapper = factory([oldFile])

    await wrapper.find('.v-input-filepond__remove-icon').trigger('click')
    wrapper.findComponent(ModalStub).vm.$emit('update:modelValue', false)
    await wrapper.vm.$nextTick()

    expect(wrapper.findComponent(ModalStub).props('modelValue')).toBe(false)
    expect(wrapper.emitted('update:modelValue')).toBeUndefined()
  })

  test('remove button is shown whenever an avatar exists', () => {
    expect(factory([oldFile]).find('.v-input-filepond__remove-icon').exists()).toBe(true)
  })

  test('remove button is hidden without an avatar or when disabled', () => {
    expect(factory([]).find('.v-input-filepond__remove-icon').exists()).toBe(false)
    expect(factory([oldFile], { disabled: true }).find('.v-input-filepond__remove-icon').exists()).toBe(false)
  })
})
