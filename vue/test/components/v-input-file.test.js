import { describe, expect, test } from 'vitest'
import { mount } from '@vue/test-utils'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

import File from '../../src/js/components/inputs/File.vue'
import i18n from '../../src/js/config/i18n'
import store from '../../src/js/store'

const vuetify = createVuetify({ components, directives })

function factory(props = {}, options = {}) {
  return mount(File, {
    global: {
      plugins: [vuetify, store, i18n],
      stubs: {
        draggable: {
          name: 'draggable',
          template: '<tbody><slot name="item" :item="{}" :index="0" /></tbody>',
        },
        FileItem: {
          name: 'FileItem',
          template: '<div class="file-item-stub" />',
        },
      },
      ...options.global,
    },
    props: {
      name: 'files',
      modelValue: [],
      max: 2,
      ...props,
    },
    ...options,
  })
}

describe('v-input-file', () => {
  test('mounts and exposes file input root class', () => {
    const wrapper = factory()
    expect(wrapper.find('.v-input-file').exists()).toBe(true)
  })

  test('renders add control when under max and model is empty', () => {
    const wrapper = factory({ modelValue: [], max: 1 })
    const buttons = wrapper.findAllComponents({ name: 'VBtn' })
    expect(buttons.length).toBeGreaterThan(0)
  })

  test('passes modelValue array through to list when files present', async () => {
    const files = [
      { id: 10, name: 'a.pdf' },
    ]
    const wrapper = factory({ modelValue: files, max: 2 })
    expect(wrapper.vm.input?.length ?? wrapper.vm.input).toBe(1)
  })
})
