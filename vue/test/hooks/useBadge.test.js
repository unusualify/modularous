import { describe, expect, test } from 'vitest'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import useBadge from '../../src/js/hooks/utils/useBadge.js'

const TestComponent = defineComponent({
  setup() {
    const { isBadge, badgeProps } = useBadge({}, {})
    return { isBadge, badgeProps }
  },
  render: () => h('div')
})

async function factory() {
  return mount(TestComponent)
}

describe('useBadge', () => {
  test('isBadge returns false when action has no badge', async () => {
    const wrapper = await factory()

    expect(wrapper.vm.isBadge({})).toBe(false)
    expect(wrapper.vm.isBadge({ name: 'foo' })).toBe(false)
  })

  test('isBadge returns badge value when badge is truthy non-number', async () => {
    const wrapper = await factory()

    expect(wrapper.vm.isBadge({ badge: true })).toBe(true)
    expect(wrapper.vm.isBadge({ badge: 'new' })).toBe('new') // non-numeric string returns as-is
  })

  test('isBadge returns truthy when badge is number > 0', async () => {
    const wrapper = await factory()

    expect(wrapper.vm.isBadge({ badge: 5 })).toBeTruthy()
    expect(wrapper.vm.isBadge({ badge: '10' })).toBeTruthy()
  })

  test('isBadge returns falsy when badge is 0', async () => {
    const wrapper = await factory()

    expect(wrapper.vm.isBadge({ badge: 0 })).toBeFalsy()
    expect(wrapper.vm.isBadge({ badge: '0' })).toBeFalsy()
  })

  test('badgeProps returns merged props', async () => {
    const wrapper = await factory()

    const action = {
      badge: 3,
      badgeContent: '3',
      badgeColor: 'error',
      badgeTextColor: 'white',
      componentProps: { density: 'compact' }
    }

    const props = wrapper.vm.badgeProps(action)

    expect(props).toMatchObject({
      content: '3',
      color: 'error',
      textColor: 'white',
      density: 'compact'
    })
  })

  test('badgeProps uses defaults when not specified', async () => {
    const wrapper = await factory()

    const props = wrapper.vm.badgeProps({ badge: 1 })

    expect(props).toMatchObject({
      content: 1,
      color: 'warning',
      textColor: 'white'
    })
  })
})
