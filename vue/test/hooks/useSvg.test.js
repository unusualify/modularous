import { describe, expect, test, vi, beforeEach } from 'vitest'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'

const svgSymbolExists = vi.fn()
const isHotSvgMode = vi.fn()
const getLocaleSymbol = vi.fn()

vi.mock('@/utils/svg', () => ({
  svgSymbolExists: (...args) => svgSymbolExists(...args),
  isHotSvgMode: (...args) => isHotSvgMode(...args),
  getLocaleSymbol: (...args) => getLocaleSymbol(...args)
}))

import useSvg from '../../src/js/hooks/useSvg.js'

const TestComponent = defineComponent({
  setup() {
    return useSvg()
  },
  render: () => h('div')
})

async function factory() {
  return mount(TestComponent)
}

describe('useSvg', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  test('returns symbolExists, isHotSvg, getLocaleSymbol', async () => {
    const wrapper = await factory()
    expect(typeof wrapper.vm.symbolExists).toBe('function')
    expect(typeof wrapper.vm.isHotSvg).toBe('function')
    expect(typeof wrapper.vm.getLocaleSymbol).toBe('function')
  })

  test('symbolExists delegates to svgSymbolExists', async () => {
    svgSymbolExists.mockReturnValue(true)
    const wrapper = await factory()
    expect(wrapper.vm.symbolExists('icon-home')).toBe(true)
    expect(svgSymbolExists).toHaveBeenCalledWith('icon-home')
  })

  test('isHotSvg delegates to isHotSvgMode', async () => {
    isHotSvgMode.mockReturnValue(true)
    const wrapper = await factory()
    expect(wrapper.vm.isHotSvg()).toBe(true)
    expect(isHotSvgMode).toHaveBeenCalled()
  })

  test('getLocaleSymbol delegates to getLocaleSymbolUtils', async () => {
    getLocaleSymbol.mockReturnValue('icon-home-en')
    const wrapper = await factory()
    expect(wrapper.vm.getLocaleSymbol('home', 'fallback')).toBe('icon-home-en')
    expect(getLocaleSymbol).toHaveBeenCalledWith('home', 'fallback')
  })
})
