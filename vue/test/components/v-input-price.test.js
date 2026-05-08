import { describe, expect, test } from 'vitest'
import { mount } from '@vue/test-utils'

import Price from '../../src/js/components/inputs/Price.vue'
import UEConfig from '../../src/js/plugins/UEConfig'

const currencyItems = [
  { id: 1, name: 'EUR', iso: 'EUR' },
  { id: 2, name: 'USD', iso: 'USD' },
]

const baseModel = [
  {
    price: 10,
    currency_id: 1,
    vat_rate_id: 1,
    price_type_id: 1,
    raw_amount: 10,
    discount_percentage: 0,
  },
]

function factory(props = {}, options = {}) {
  return mount(Price, {
    global: {
      plugins: [UEConfig],
      stubs: {
        CurrencyNumber: {
          name: 'CurrencyNumber',
          template: '<div class="currency-number-stub"><slot name="append-inner" /></div>',
        },
      },
      ...options.global,
    },
    props: {
      name: 'price',
      priceInputName: 'price',
      modelValue: baseModel,
      items: currencyItems,
      vatRates: [],
      showVatRate: false,
      hasDiscount: false,
      ...props,
    },
    attachTo: document.body,
    ...options,
  })
}

describe('v-input-price', () => {
  test('mounts with expected component name', () => {
    const wrapper = factory()
    expect(wrapper.vm.$options.name).toBe('v-input-price')
  })

  test('renders price row with v-input-price class', () => {
    const wrapper = factory()
    expect(wrapper.find('.v-input-price').exists()).toBe(true)
  })

  test('deepModel reflects one row for single price entry', () => {
    const wrapper = factory()
    expect(wrapper.vm.deepModel.length).toBe(1)
    expect(wrapper.vm.deepModel[0].price).toBe(10)
  })

  test('shows currency chip text from items', () => {
    const wrapper = factory()
    const chip = wrapper.findComponent({ name: 'VChip' })
    expect(chip.exists()).toBe(true)
    expect(chip.text()).toContain('EUR')
  })
})
