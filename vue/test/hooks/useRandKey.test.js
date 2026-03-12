import { describe, expect, test, vi, beforeEach, afterEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { defineComponent, h } from 'vue'
import useRandKey from '../../src/js/hooks/useRandKey.js'

const TestComponent = defineComponent({
  setup () {
    return { randKey: useRandKey() }
  },
  render: () => h('div')
})

describe('useRandKey', () => {
  let dateNowSpy
  let randomSpy

  beforeEach(() => {
    dateNowSpy = vi.spyOn(Date, 'now').mockReturnValue(1700000000000)
    randomSpy = vi.spyOn(Math, 'random').mockReturnValue(0.5)
  })

  afterEach(() => {
    dateNowSpy.mockRestore()
    randomSpy.mockRestore()
  })

  test('returns randKey from setup', async () => {
    const wrapper = mount(TestComponent)

    expect(wrapper.vm.randKey).toBeDefined()
    expect(typeof wrapper.vm.randKey).toBe('number')
  })

  test('randKey is based on Date.now and random', async () => {
    const wrapper = mount(TestComponent)

    expect(wrapper.vm.randKey).toBe(1700000000000 + Math.floor(0.5 * 9999))
  })
})
