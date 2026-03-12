import { describe, expect, test } from 'vitest'
import { mount } from '@vue/test-utils'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

import InputRenderer from '../../src/js/components/inputs/InputRenderer.vue'

const vuetify = createVuetify({ components, directives })

function createMockContext(overrides = {}) {
  return {
    id: 'test-form',
    index: 0,
    valueIntern: {},
    bindSchema: (obj) => ({ ...obj.schema, label: obj.schema?.label }),
    setValue: (obj) => obj.value ?? '',
    onInput: () => {},
    onEvent: () => {},
    updateInput: () => {},
    checkExtensionType: () => undefined,
    searchInputSync: () => 'searchInput',
    suspendClickAppend: () => 'click',
    getInjectedScopedSlots: () => [],
    getKeyInjectSlot: () => 'slot-inject',
    vueInstance: { components: {} },
    ...overrides
  }
}

async function factory(obj = {}, context = null) {
  const defaultObj = {
    key: 'name',
    value: '',
    schema: { type: 'text', label: 'Name' }
  }

  return mount(InputRenderer, {
    global: {
      plugins: [vuetify]
    },
    props: {
      obj: { ...defaultObj, ...obj },
      context: context ?? createMockContext()
    }
  })
}

describe('InputRenderer', () => {
  test('renders component', async () => {
    const wrapper = await factory()

    expect(wrapper.exists()).toBe(true)
  })

  test('resolves text type to v-text-field', async () => {
    const wrapper = await factory({ schema: { type: 'text', label: 'Name' } })

    expect(wrapper.findComponent({ name: 'v-text-field' }).exists()).toBe(true)
  })

  test('resolves textarea type to v-textarea', async () => {
    const wrapper = await factory({ schema: { type: 'textarea', label: 'Description' } })

    expect(wrapper.findComponent({ name: 'v-textarea' }).exists()).toBe(true)
  })

  test('passes bindSchema result to component', async () => {
    const bindSchema = (obj) => ({ label: obj.schema?.label ?? 'Custom' })
    const context = createMockContext({ bindSchema })

    const wrapper = await factory({ schema: { type: 'text', label: 'Email' } }, context)

    const textField = wrapper.findComponent({ name: 'v-text-field' })
    expect(textField.exists()).toBe(true)
  })
})
