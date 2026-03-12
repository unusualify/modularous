import { describe, expect, test } from 'vitest'
import { mount } from '@vue/test-utils'
import store from '../../src/js/store'
import useCache from '../../src/js/hooks/useCache.js'

const TestComponent = {
  setup() {
    return useCache()
  },
  template: '<div />'
}

async function factory() {
  return mount(TestComponent, {
    global: { plugins: [store] }
  })
}

describe('useCache', () => {
  test('returns get, put, push, last, forget, states', async () => {
    const wrapper = await factory()
    const vm = wrapper.vm
    expect(typeof vm.get).toBe('function')
    expect(typeof vm.put).toBe('function')
    expect(typeof vm.push).toBe('function')
    expect(typeof vm.last).toBe('function')
    expect(typeof vm.forget).toBe('function')
    expect(vm.states).toBeDefined()
  })

  test('get returns defaultValue when key missing', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.get('missing', 'default')).toBe('default')
  })

  test('put and get roundtrip', async () => {
    const wrapper = await factory()
    wrapper.vm.put('foo', 'bar')
    expect(wrapper.vm.get('foo')).toBe('bar')
  })

  test('push appends to array', async () => {
    const wrapper = await factory()
    wrapper.vm.push('arr', 1)
    wrapper.vm.push('arr', 2)
    expect(wrapper.vm.last('arr')).toBe(2)
    expect(wrapper.vm.get('arr')).toEqual([1, 2])
  })

  test('forget removes key', async () => {
    const wrapper = await factory()
    wrapper.vm.put('temp', 'value')
    expect(wrapper.vm.get('temp')).toBe('value')
    wrapper.vm.forget('temp')
    expect(wrapper.vm.get('temp', null)).toBe(null)
  })
})
