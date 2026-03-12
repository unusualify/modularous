import { describe, expect, test } from 'vitest'
import { mount } from '@vue/test-utils'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

import { VAlert } from 'vuetify/components/VAlert'

const vuetify = createVuetify({ components, directives })

describe('VAlert tests', () => {
  test('renders vuetify v-alert', () => {
    const wrapper = mount(VAlert, {
      global: {
        plugins: [vuetify]
      },
      props: {
        density: 'compact',
        type: 'warning',
        text: 'Example Alert'
      }
    })

    expect(wrapper.text()).toBe('Example Alert')
  })
})

