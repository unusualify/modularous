import { describe, expect, test, vi } from 'vitest'
import { defineComponent, ref } from 'vue'
import { mount } from '@vue/test-utils'
import i18n from '../../src/js/config/i18n'
import useFormatter, { makeFormatterProps } from '../../src/js/hooks/useFormatter.js'

const TestComponent = defineComponent({
  props: {
    ignoreFormatters: { type: Array, default: () => [] }
  },
  setup(props, context) {
    return useFormatter(props, context, { value: [] })
  },
  template: '<div />'
})

const TestComponentWithHeaders = defineComponent({
  props: {
    ignoreFormatters: { type: Array, default: () => [] }
  },
  setup(props, context) {
    const headers = ref([
      { key: 'name', formatter: ['chip'] },
      { key: 'status', formatter: ['status'] },
      { key: 'ignored', formatter: ['ignored'] }
    ])
    return useFormatter(props, context, headers)
  },
  template: '<div />'
})

const TestComponentWithFormatterName = defineComponent({
  props: {
    ignoreFormatters: { type: Array, default: () => [] }
  },
  setup(props, context) {
    const headers = ref([
      { key: 'edit', formatter: ['edit'], formatterName: 'edit' },
      { key: 'activate', formatter: ['activate'], formatterName: 'activate' }
    ])
    return useFormatter(props, context, headers)
  },
  template: '<div />'
})

async function factory(props = {}) {
  return mount(TestComponent, {
    global: { plugins: [i18n] },
    props
  })
}

async function factoryWithHeaders(props = {}) {
  return mount(TestComponentWithHeaders, {
    global: { plugins: [i18n] },
    props
  })
}

describe('useFormatter', () => {
  test('makeFormatterProps returns ignoreFormatters prop definition', () => {
    const props = makeFormatterProps()
    expect(props.ignoreFormatters).toBeDefined()
    expect(props.ignoreFormatters.type).toContain(Array)
  })

  test('returns formatterColumns, handleFormatter, and formatter methods', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.formatterColumns).toEqual([])
    expect(typeof wrapper.vm.handleFormatter).toBe('function')
    expect(typeof wrapper.vm.pascalFormatter).toBe('function')
    expect(typeof wrapper.vm.chipFormatter).toBe('function')
    expect(typeof wrapper.vm.badgeFormatter).toBe('function')
    expect(typeof wrapper.vm.makeText).toBe('function')
  })

  test('formatterColumns filters and maps headers with formatter array', async () => {
    const wrapper = await factoryWithHeaders({ ignoreFormatters: ['ignored'] })
    const cols = wrapper.vm.formatterColumns
    expect(cols.length).toBe(2)
    expect(cols.find(c => c.key === 'ignored')).toBeUndefined()
    expect(cols.find(c => c.key === 'name').formatterName).toBe('chip')
    expect(cols.find(c => c.key === 'status').formatterName).toBe('status')
  })

  test('formatterColumns includes formatterName edit/activate when both formatter and formatterName present', async () => {
    const wrapper = mount(TestComponentWithFormatterName, {
      global: { plugins: [i18n] }
    })
    const cols = wrapper.vm.formatterColumns
    expect(cols.length).toBe(2)
    expect(cols.find(c => c.formatterName === 'edit')).toBeDefined()
    expect(cols.find(c => c.formatterName === 'activate')).toBeDefined()
  })

  test('pascalFormatter converts to PascalCase', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.pascalFormatter('hello world')).toBe('HelloWorld')
    expect(wrapper.vm.pascalFormatter('foo_bar')).toBe('FooBar')
  })

  test('dateFormatter returns formatted date configuration', async () => {
    const wrapper = await factory()
    const result = wrapper.vm.dateFormatter('2024-01-15T12:00:00Z', 'short')
    expect(result.configuration).toBeDefined()
    expect(result.configuration.elements).toBeDefined()
  })

  test('chipFormatter returns chip configuration', async () => {
    const wrapper = await factory()
    const result = wrapper.vm.chipFormatter('Label', { color: 'primary' })
    expect(result.configuration.tag).toBe('v-chip')
    expect(result.configuration.elements).toBe('Label')
  })

  test('badgeFormatter returns badge configuration', async () => {
    const wrapper = await factory()
    const result = wrapper.vm.badgeFormatter(3)
    expect(result.configuration.tag).toBe('v-badge')
    expect(result.configuration.attributes.content).toBe(3)
  })

  test('makeChip returns chip configuration', async () => {
    const wrapper = await factory()
    const result = wrapper.vm.makeChip('Label', { color: 'primary' })
    expect(result).toEqual({
      tag: 'v-chip',
      attributes: { color: 'primary' },
      elements: 'Label'
    })
  })

  test('makeBadge returns badge configuration', async () => {
    const wrapper = await factory()
    const result = wrapper.vm.makeBadge(5)
    expect(result.tag).toBe('v-badge')
    expect(result.attributes.content).toBe(5)
  })

  test('makeText returns text configuration', async () => {
    const wrapper = await factory()
    const result = wrapper.vm.makeText('Hello')
    expect(result).toEqual({ elements: 'Hello' })
  })

  test('editFormatter returns edit configuration', async () => {
    const wrapper = await factory()
    const result = wrapper.vm.editFormatter('Item 1')
    expect(result.configuration.tag).toBe('span')
    expect(result.configuration.elements).toBe('Item 1')
    expect(result.configuration.attributes.onClick).toBe('editItem')
  })

  test('priceFormatter returns price configuration with unit and taxContent', async () => {
    const wrapper = await factory()
    const result = wrapper.vm.priceFormatter(100, '₺', 'VAT')
    expect(result.configuration.elements).toHaveLength(2)
    expect(result.configuration.elements[0].elements).toBe('₺100')
    expect(result.configuration.elements[1].elements).toBe('+VAT')
  })

  test('priceFormatter returns price configuration without taxContent', async () => {
    const wrapper = await factory()
    const result = wrapper.vm.priceFormatter(50, '$')
    expect(result.configuration.elements[1].elements).toBe('')
  })

  test('statusFormatter returns check icon for truthy values', async () => {
    const wrapper = await factory()
    for (const val of [true, 'true', 1, '1']) {
      const result = wrapper.vm.statusFormatter(val)
      expect(result.configuration.attributes.icon).toBe('mdi-check')
      expect(result.configuration.attributes.style.color).toBe('green')
    }
  })

  test('statusFormatter returns close icon for falsy', async () => {
    const wrapper = await factory()
    const result = wrapper.vm.statusFormatter(false)
    expect(result.configuration.attributes.icon).toBe('mdi-close')
    expect(result.configuration.attributes.style.color).toBe('red')
  })

  test('shortenFormatter shortens value', async () => {
    const wrapper = await factory()
    const result = wrapper.vm.shortenFormatter('hello world', 5)
    expect(result.configuration.elements).toBe('hello...')
  })

  test('handleFormatter calls formatter by name', async () => {
    const wrapper = await factory()
    const result = wrapper.vm.handleFormatter(['chip'], 'Label')
    expect(result.configuration.tag).toBe('v-chip')
    expect(result.configuration.elements).toBe('Label')
  })

  test('handleFormatter with formatter args passes them through', async () => {
    const wrapper = await factory()
    const result = wrapper.vm.handleFormatter(['shorten', 5], 'hello world')
    expect(result.configuration.elements).toBe('hello...')
  })

  test('handleFormatter returns empty for null/undefined/empty string', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.handleFormatter(['makeText'], null)).toEqual({
      configuration: { elements: '' }
    })
    expect(wrapper.vm.handleFormatter(['makeText'], undefined)).toEqual({
      configuration: { elements: '' }
    })
    expect(wrapper.vm.handleFormatter(['makeText'], '')).toEqual({
      configuration: { elements: '' }
    })
  })

  test('handleFormatter catches and logs error for unknown formatter', async () => {
    const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {})
    const wrapper = await factory()
    const result = wrapper.vm.handleFormatter(['unknownFormatter'], 'value')
    expect(consoleSpy).toHaveBeenCalled()
    consoleSpy.mockRestore()
  })
})
