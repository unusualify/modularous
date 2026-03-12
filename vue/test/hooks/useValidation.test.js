import { describe, expect, test } from 'vitest'
import { defineComponent, h, reactive } from 'vue'
import { mount } from '@vue/test-utils'
import useValidation from '../../src/js/hooks/useValidation.js'
import i18n from '../../src/js/config/i18n'

const TestComponent = defineComponent({
  setup() {
    const validation = useValidation(reactive({}))
    return validation
  },
  render: () => h('div')
})

async function factory() {
  return mount(TestComponent, {
    global: {
      plugins: [i18n]
    }
  })
}

describe('useValidation', () => {
  test('minRule returns true when value meets min length', async () => {
    const wrapper = await factory()
    const rule = wrapper.vm.minRule(3)

    expect(rule('abc')).toBe(true)
    expect(rule('ab')).not.toBe(true)
  })

  test('minRule trims string before checking', async () => {
    const wrapper = await factory()
    const rule = wrapper.vm.minRule(3)

    expect(rule('  abc  ')).toBe(true)
  })

  test('maxRule returns true when value is within max', async () => {
    const wrapper = await factory()
    const rule = wrapper.vm.maxRule(5)

    expect(rule('abc')).toBe(true)
    expect(rule('')).toBe(true)
    expect(rule('abcdef')).not.toBe(true)
  })

  test('requiredRule returns true when value is present', async () => {
    const wrapper = await factory()
    const rule = wrapper.vm.requiredRule()

    expect(rule('value')).toBe(true)
    expect(rule('')).not.toBe(true)
    expect(rule(null)).not.toBe(true)
  })

  test('emailRule returns true for valid email', async () => {
    const wrapper = await factory()
    const rule = wrapper.vm.emailRule()

    expect(rule('user@example.com')).toBe(true)
    expect(rule('')).toBe(true)
    expect(rule('invalid')).not.toBe(true)
    expect(rule('missing@domain')).not.toBe(true)
  })

  test('emailRule validates domain when allowedDomains specified', async () => {
    const wrapper = await factory()
    const rule = wrapper.vm.emailRule({ allowedDomains: ['example.com'] })

    expect(rule('user@example.com')).toBe(true)
    expect(rule('user@other.com')).not.toBe(true)
  })

  test('nameRule returns true for valid names', async () => {
    const wrapper = await factory()
    const rule = wrapper.vm.nameRule()
    expect(rule('John Doe')).toBe(true)
    expect(rule('Mary-Jane')).toBe(true)
    expect(rule("O'Brien")).toBe(true)
  })

  test('nameRule returns error for invalid names', async () => {
    const wrapper = await factory()
    const rule = wrapper.vm.nameRule()
    expect(rule('John123')).not.toBe(true)
    expect(rule('')).toBe(true)
  })

  test('requiredRule array type validates array length', async () => {
    const wrapper = await factory()
    const rule = wrapper.vm.requiredRule('array', 1)
    expect(rule([1])).toBe(true)
    expect(rule([])).not.toBe(true)
  })

  test('arrayRule returns true for arrays', async () => {
    const wrapper = await factory()
    const rule = wrapper.vm.arrayRule()
    expect(rule([1, 2])).toBe(true)
    expect(rule({})).not.toBe(true)
  })

  test('numberRule returns true for valid numbers', async () => {
    const wrapper = await factory()
    const rule = wrapper.vm.numberRule()
    expect(rule(42)).toBe(true)
    expect(rule('3.14')).toBe(true)
    expect(rule('abc')).not.toBe(true)
  })

  test('integerRule returns true for integers', async () => {
    const wrapper = await factory()
    const rule = wrapper.vm.integerRule()
    expect(rule(42)).toBe(true)
    expect(rule('42')).toBe(true)
    expect(rule(3.14)).not.toBe(true)
  })

  test('urlRule returns true for valid URLs', async () => {
    const wrapper = await factory()
    const rule = wrapper.vm.urlRule()
    expect(rule('https://example.com')).toBe(true)
    expect(rule('http://test.org/path')).toBe(true)
    expect(rule('')).toBe(true)
    expect(rule('not-a-url')).not.toBe(true)
  })

  test('phoneRule returns true for valid phone', async () => {
    const wrapper = await factory()
    const rule = wrapper.vm.phoneRule()
    expect(rule('123-456-7890')).toBe(true)
    expect(rule('')).toBe(true)
    expect(rule('abc')).not.toBe(true)
  })

  test('alphaRule returns true for letters only', async () => {
    const wrapper = await factory()
    const rule = wrapper.vm.alphaRule()
    expect(rule('abc')).toBe(true)
    expect(rule('ABC')).toBe(true)
    expect(rule('')).toBe(true)
    expect(rule('abc123')).not.toBe(true)
  })

  test('alphaNumRule returns true for letters and numbers', async () => {
    const wrapper = await factory()
    const rule = wrapper.vm.alphaNumRule()
    expect(rule('abc123')).toBe(true)
    expect(rule('')).toBe(true)
    expect(rule('abc-123')).not.toBe(true)
  })

  test('dateRule returns true for valid date', async () => {
    const wrapper = await factory()
    const rule = wrapper.vm.dateRule()
    expect(rule('2024-01-15')).toBe(true)
    expect(rule('')).toBe(true)
    expect(rule('invalid')).not.toBe(true)
  })

  test('equalsRule and notEqualsRule', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.equalsRule('target')('target')).toBe(true)
    expect(wrapper.vm.equalsRule('target')('other')).not.toBe(true)
    expect(wrapper.vm.notEqualsRule('target')('other')).toBe(true)
    expect(wrapper.vm.notEqualsRule('target')('target')).not.toBe(true)
  })

  test('betweenRule and minValueRule maxValueRule', async () => {
    const wrapper = await factory()
    const between = wrapper.vm.betweenRule(1, 10)
    expect(between(5)).toBe(true)
    expect(between(11)).not.toBe(true)
    expect(between(-1)).not.toBe(true)
    expect(wrapper.vm.minValueRule(5)(10)).toBe(true)
    expect(wrapper.vm.minValueRule(5)(3)).not.toBe(true)
    expect(wrapper.vm.maxValueRule(10)(5)).toBe(true)
    expect(wrapper.vm.maxValueRule(10)(15)).not.toBe(true)
  })

  test('containsRule startsWithRule endsWithRule', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.containsRule('foo')('hello foo')).toBe(true)
    expect(wrapper.vm.containsRule('foo')('hello')).not.toBe(true)
    expect(wrapper.vm.startsWithRule('hello')('hello world')).toBe(true)
    expect(wrapper.vm.endsWithRule('world')('hello world')).toBe(true)
  })

  test('uniqueRule validates unique arrays', async () => {
    const wrapper = await factory()
    const rule = wrapper.vm.uniqueRule()
    expect(rule([1, 2, 3])).toBe(true)
    expect(rule([1, 1, 2])).not.toBe(true)
    expect(rule('')).toBe(true)
  })

  test('generateInputRules and validateInput', async () => {
    const wrapper = await factory()
    const rules = wrapper.vm.generateInputRules({ name: 'test', rules: 'required|min:3' })
    expect(rules.length).toBeGreaterThan(0)
    expect(wrapper.vm.validateInput({ name: 'test', rules: 'required' }, 'value')).toBe(true)
    expect(wrapper.vm.validateInput({ name: 'test', rules: 'required' }, '')).not.toBe(true)
  })

  test('invokeRuleGenerator processes schema with rules', async () => {
    const wrapper = await factory()
    const schema = {
      name: { name: 'name', rules: 'required' },
      email: { name: 'email', rules: 'email' }
    }
    const result = wrapper.vm.invokeRuleGenerator(schema)
    expect(result.name.rules).toBeDefined()
    expect(Array.isArray(result.name.rules)).toBe(true)
  })

  test('invokeRule processes single input with rules', async () => {
    const wrapper = await factory()
    const input = { name: 'test', rules: 'required' }
    const result = wrapper.vm.invokeRule(input)
    expect(result).toBeDefined()
    expect(result.name).toBe('test')
    expect(result.rawRules).toBeDefined()
  })
})
