import { describe, expect, test } from 'vitest'
import { mount } from '@vue/test-utils'

import Filepond from '../../src/js/components/inputs/Filepond.vue'
import UEConfig from '../../src/js/plugins/UEConfig'

function factory(props = {}, options = {}) {
  return mount(Filepond, {
    global: {
      plugins: [UEConfig],
      stubs: {
        FilePond: {
          name: 'FilePond',
          template: '<div class="filepond-stub" data-test="filepond-stub" />',
        },
        UeTitle: {
          name: 'UeTitle',
          template: '<span><slot /></span>',
        },
      },
      ...options.global,
    },
    props: {
      name: 'upload',
      modelValue: [],
      endPoints: { load: '/api/media/load/' },
      ...props,
    },
    attachTo: document.body,
    ...options,
  })
}

describe('v-input-filepond', () => {
  test('mounts with expected component name', () => {
    const wrapper = factory()
    expect(wrapper.vm.$options.name).toBe('v-input-filepond')
  })

  test('renders stubbed FilePond region', () => {
    const wrapper = factory({ label: 'Upload' })
    expect(wrapper.find('[data-test="filepond-stub"]').exists()).toBe(true)
  })

  test('applies file field class on wrapper content', () => {
    const wrapper = factory()
    expect(wrapper.find('.v-input-filepond__file-field').exists()).toBe(true)
  })
})
