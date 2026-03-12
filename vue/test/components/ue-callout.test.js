import { describe, expect, test } from 'vitest'
import { mount } from '@vue/test-utils'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

import Callout from '../../src/js/components/labs/Callout.vue'

const vuetify = createVuetify({ components, directives })

describe('callout tests', () => {
  test('renders Callout component with title and value', () => {
    const wrapper = mount(Callout, {
      global: {
        plugins: [vuetify],
        stubs: {
          RowFormat: { template: '<div class="row-format"><slot /></div>' }
        }
      },
      props: {
        title: 'Callout Title',
        value: 'Callout Value'
      }
    })

    expect(wrapper.exists()).toBe(true)
    expect(wrapper.find('.v-sheet').exists()).toBe(true)
    expect(wrapper.find('.v-alert').exists()).toBe(true)
  })

  test('renders with default props', () => {
    const wrapper = mount(Callout, {
      global: {
        plugins: [vuetify],
        stubs: {
          RowFormat: { template: '<div class="row-format" />' }
        }
      }
    })

    expect(wrapper.exists()).toBe(true)
  })
})

