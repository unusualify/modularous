import { describe, expect, test } from 'vitest'
import { mount } from '@vue/test-utils'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

import FormPreviewFields from '../../src/js/components/form/FormPreviewFields.vue'

const vuetify = createVuetify({ components, directives })

const schema = {
  is_personal: { type: 'checkbox', name: 'is_personal', label: "I don't have a company", trueValue: 1, falseValue: 0 },
  name: { type: 'text', name: 'name', label: 'Company Name', col: { cols: 12, sm: 6 } },
}

const mountPreview = (model) => mount(FormPreviewFields, {
  props: { schema, model },
  global: { plugins: [vuetify] },
})

describe('FormPreviewFields', () => {
  test('renders boolean inputs as a read-only switch row', () => {
    const wrapper = mountPreview({ is_personal: 0, name: 'Acme Inc' })
    const row = wrapper.find('.ue-form-preview__col--boolean')

    expect(row.exists()).toBe(true)
    expect(row.text()).toContain("I don't have a company")

    const input = row.find('input[type="checkbox"]')
    expect(input.exists()).toBe(true)
    expect(input.element.checked).toBe(false)
    expect(row.find('.v-input--readonly').exists()).toBe(true)
  })

  test('switch reflects true value', () => {
    const wrapper = mountPreview({ is_personal: 1, name: '' })

    expect(wrapper.find('.ue-form-preview__col--boolean input').element.checked).toBe(true)
  })

  test('non-boolean inputs keep label/value layout', () => {
    const wrapper = mountPreview({ is_personal: 0, name: 'Acme Inc' })

    expect(wrapper.find('.ue-form-preview__value').text()).toBe('Acme Inc')
  })
})
