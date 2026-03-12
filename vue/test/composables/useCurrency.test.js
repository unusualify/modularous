import { describe, expect, test } from 'vitest'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import useCurrency from '../../src/js/hooks/useCurrency.js'
import i18n from '../../src/js/config/i18n'

const TestComponent = defineComponent({
  template: '<div>{{ formatPrice(amount, symbol) }}</div>',
  props: {
    amount: { type: Number, default: 0 },
    symbol: { type: String, default: '€' }
  },
  setup (props) {
    const { formatPrice } = useCurrency(props)
    return { formatPrice }
  }
})

async function factory(props = {}) {
  return mount(TestComponent, {
    global: {
      plugins: [i18n]
    },
    props: {
      amount: 1234.56,
      symbol: '€',
      ...props
    }
  })
}

describe('useCurrency composable', () => {
  test('formatPrice formats amount with symbol', async () => {
    const wrapper = await factory({ amount: 1234.56, symbol: '€' })

    expect(wrapper.text()).toContain('€')
    // Locale may format as 1,234.56 or 1.234,56
    expect(wrapper.text()).toMatch(/1[.,]?234[.,]?56/)
  })

  test('formatPrice handles zero', async () => {
    const wrapper = await factory({ amount: 0, symbol: '$' })

    expect(wrapper.text()).toContain('$')
    expect(wrapper.text()).toMatch(/\d/)
  })
})
