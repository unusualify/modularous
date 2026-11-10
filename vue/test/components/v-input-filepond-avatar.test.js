import { describe, expect, test } from 'vitest'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

import FilepondAvatar from '../../src/js/components/inputs/FilepondAvatar.vue'

const vuetify = createVuetify({ components, directives })

// Stub the inner filepond; tests emit update:modelValue to simulate FilePond.
const FilepondStub = defineComponent({
  name: 'VInputFilepond',
  props: { modelValue: { type: Array, default: () => [] } },
  emits: ['update:modelValue'],
  setup (props, { emit, expose }) {
    expose({ browse: () => {} })
    return () => h('div', { class: 'filepond-stub' })
  },
})

const factory = (modelValue) => mount(FilepondAvatar, {
  props: { name: 'avatar', modelValue },
  global: { plugins: [vuetify], stubs: { VInputFilepond: FilepondStub, 'v-input-filepond': FilepondStub } },
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
})
