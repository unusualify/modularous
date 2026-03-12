import { describe, expect, test } from 'vitest'
import { createStore } from 'vuex'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import useAlert from '../../src/js/hooks/useAlert.js'
import alertModule from '../../src/js/store/modules/alert.js'

function createStoreWithAlert() {
  return createStore({
    modules: {
      alert: alertModule
    }
  })
}

const TestComponent = defineComponent({
  setup() {
    const { openAlert } = useAlert()
    return { openAlert }
  },
  render: () => h('div')
})

async function factory() {
  return mount(TestComponent, {
    global: { plugins: [createStoreWithAlert()] }
  })
}

describe('useAlert', () => {
  test('returns openAlert function', async () => {
    const wrapper = await factory()
    expect(typeof wrapper.vm.openAlert).toBe('function')
  })

  test('openAlert commits SET_ALERT with payload', async () => {
    const store = createStoreWithAlert()
    const spy = vi.spyOn(store, 'commit')
    const wrapper = mount(TestComponent, {
      global: { plugins: [store] }
    })
    const payload = { message: 'Test alert', variant: 'success' }
    wrapper.vm.openAlert(payload)
    expect(spy).toHaveBeenCalledWith('__setAlert', payload)
  })
})
