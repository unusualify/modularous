import { describe, expect, test } from 'vitest'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import i18n from '../../src/js/config/i18n'
import useModule, { makeModuleProps } from '../../src/js/hooks/useModule.js'

const TestComponent = defineComponent({
  props: {
    name: { type: String, default: 'Posts' },
    moduleName: { type: String },
    routeName: { type: String },
    isModuleRoute: { type: Boolean, default: false },
    items: { type: Array, default: () => [] }
  },
  setup(props, context) {
    return useModule(props, context)
  },
  render: () => h('div')
})

async function factory(props = {}) {
  return mount(TestComponent, {
    global: { plugins: [i18n] },
    props: { name: 'Posts', ...props }
  })
}

describe('useModule', () => {
  test('returns moduleSnakeName, routeSnakeName, snakeName', async () => {
    const wrapper = await factory({ name: 'PostList' })
    expect(wrapper.vm.moduleSnakeName).toBe('post_list')
    expect(wrapper.vm.snakeName).toBe('post_list')
  })

  test('tableTranslationNotation for non-module route', async () => {
    const wrapper = await factory({ name: 'Posts' })
    expect(wrapper.vm.tableTranslationNotation).toContain('modules')
    expect(wrapper.vm.tableTranslationNotation).toContain('posts')
  })

  test('tableTranslationNotation for module route with moduleName and routeName', async () => {
    const wrapper = await factory({
      isModuleRoute: true,
      moduleName: 'Cms',
      routeName: 'Pages'
    })
    expect(wrapper.vm.tableTranslationNotation).toContain('cms')
    expect(wrapper.vm.tableTranslationNotation).toContain('pages')
  })

  test('permissionName uses kebab-case', async () => {
    const wrapper = await factory({ name: 'PostList' })
    expect(wrapper.vm.permissionName).toBe('post-list')
  })

  test('onResize updates windowSize', async () => {
    const wrapper = await factory()
    Object.defineProperty(window, 'innerWidth', { value: 1024, writable: true })
    Object.defineProperty(window, 'innerHeight', { value: 768, writable: true })
    wrapper.vm.onResize()
    expect(wrapper.vm.windowSize).toEqual({ x: 1024, y: 768 })
  })

  test('makeModuleProps defines expected props', () => {
    const props = makeModuleProps()
    expect(props.name).toBeDefined()
    expect(props.titlePrefix.default).toBe('')
    expect(props.items.default).toEqual([])
  })
})
