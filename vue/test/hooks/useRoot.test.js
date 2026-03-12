import { describe, expect, test } from 'vitest'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import useRoot from '../../src/js/hooks/useRoot.js'

const vuetify = createVuetify({ components, directives })

const TestComponent = defineComponent({
  setup() {
    return useRoot()
  },
  template: '<div />'
})

describe('useRoot', () => {
  test('returns state and methods', async () => {
    const wrapper = mount(TestComponent, {
      global: { plugins: [vuetify] }
    })
    await wrapper.vm.$nextTick()
    expect(wrapper.vm).toBeDefined()
    expect(typeof wrapper.vm.onMounted).toBe('undefined')
  })
})
