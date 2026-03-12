import { describe, expect, test } from 'vitest'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import i18n from '../../src/js/config/i18n'
import useCastAttributes from '../../src/js/hooks/useCastAttributes.js'

const TestComponent = defineComponent({
  setup() {
    return useCastAttributes()
  },
  render: () => h('div')
})

async function factory() {
  return mount(TestComponent, {
    global: { plugins: [i18n] }
  })
}

describe('useCastAttributes', () => {
  test('matchAttribute returns true for ${variable}$ pattern', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.matchAttribute('${foo}$')).toBe(true)
    expect(wrapper.vm.matchAttribute('plain')).toBe(false)
  })

  test('matchStandardAttribute returns true for $variable pattern', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.matchStandardAttribute('$name')).toBe(true)
    expect(wrapper.vm.matchStandardAttribute('$user.id')).toBe(true)
    expect(wrapper.vm.matchStandardAttribute('plain')).toBe(false)
  })

  test('matchEvalAttribute returns true for $(expr)$ pattern', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.matchEvalAttribute('$(1 + 2)$')).toBe(true)
    expect(wrapper.vm.matchEvalAttribute('$foo')).toBe(false)
  })

  test('matchAnyPattern returns true if any pattern matches', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.matchAnyPattern('${foo}$')).toBe(true)
    expect(wrapper.vm.matchAnyPattern('$name')).toBe(true)
    expect(wrapper.vm.matchAnyPattern('$(1)$')).toBe(true)
    expect(wrapper.vm.matchAnyPattern('plain')).toBe(false)
  })

  test('castAttribute returns replaced value for ${pattern}$', async () => {
    const wrapper = await factory()
    const ownerItem = { name: 'John', id: 1 }
    const result = wrapper.vm.castAttribute('${name}$', ownerItem)
    expect(result).toBe('John')
  })

  test('castAttribute returns value unchanged when no pattern', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.castAttribute('plain', {})).toBe('plain')
  })

  test('castObjectAttributes returns value for string', async () => {
    const wrapper = await factory()
    const ownerItem = { title: 'Test' }
    const result = wrapper.vm.castObjectAttributes('${title}$', ownerItem)
    expect(result).toBe('Test')
  })

  test('castObjectAttributes maps over array', async () => {
    const wrapper = await factory()
    const result = wrapper.vm.castObjectAttributes(['a', 'b'], {})
    expect(result).toEqual(['a', 'b'])
  })

  test('castObjectAttributes reduces over object', async () => {
    const wrapper = await factory()
    const result = wrapper.vm.castObjectAttributes({ a: 1, b: 2 }, {})
    expect(result).toEqual({ a: 1, b: 2 })
  })

  test('castStandardAttribute replaces $name with value', async () => {
    const wrapper = await factory()
    const ownerItem = { name: 'John', id: 1 }
    const result = wrapper.vm.castStandardAttribute('$name', ownerItem)
    expect(result).toBe('John')
  })

  test('castStandardAttribute replaces nested $user.id', async () => {
    const wrapper = await factory()
    const ownerItem = { user: { id: 42 } }
    const result = wrapper.vm.castStandardAttribute('$user.id', ownerItem)
    expect(result).toBe('42')
  })

  test('castStandardAttribute returns value unchanged when no match', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.castStandardAttribute('plain text', {})).toBe('plain text')
  })

  test('castEvalAttribute evaluates $(expr)$', async () => {
    const wrapper = await factory()
    const ownerItem = { a: 2, b: 3 }
    const result = wrapper.vm.castEvalAttribute('$(1 + 2)$', ownerItem)
    expect(result).toBe(3)
  })

  test('castObjectAttribute replaces ${key}$ in string', async () => {
    const wrapper = await factory()
    const ownerItem = { title: 'Hello' }
    const result = wrapper.vm.castObjectAttribute('${title}$', ownerItem)
    expect(result).toBe('Hello')
  })
})
