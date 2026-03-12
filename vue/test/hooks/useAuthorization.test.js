import { describe, expect, test, vi } from 'vitest'
import { createStore } from 'vuex'
import { mount } from '@vue/test-utils'
import { defineComponent, h } from 'vue'
import useAuthorization from '../../src/js/hooks/useAuthorization.js'

function createMockStore(overrides = {}) {
  const defaultGetters = {
    isSuperAdmin: () => false,
    userPermissions: () => ({
      'edit_post': true,
      'delete_post': false
    }),
    userProfile: () => ({ id: 42 }),
    userRoles: () => ['admin', 'editor'],
    ...overrides.getters
  }
  return createStore({
    state: {},
    getters: defaultGetters,
    ...overrides
  })
}

const TestComponent = defineComponent({
  setup() {
    const { can, hasRoles, isYou } = useAuthorization()
    return { can, hasRoles, isYou }
  },
  render: () => h('div')
})

async function factory(store) {
  return mount(TestComponent, {
    global: {
      plugins: [store]
    }
  })
}

describe('useAuthorization', () => {
  test('can returns true when user has permission', async () => {
    const store = createMockStore()
    const wrapper = await factory(store)

    expect(wrapper.vm.can('edit_post')).toBe(true)
  })

  test('can returns false when user lacks permission', async () => {
    const store = createMockStore()
    const wrapper = await factory(store)

    expect(wrapper.vm.can('delete_post')).toBe(false)
  })

  test('can returns true when isSuperAdmin', async () => {
    const store = createMockStore({
      getters: {
        isSuperAdmin: () => true,
        userPermissions: () => ({}),
        userProfile: () => ({ id: 1 }),
        userRoles: () => []
      }
    })
    const wrapper = await factory(store)

    expect(wrapper.vm.can('any_permission')).toBe(true)
  })

  test('can supports moduleName prefix', async () => {
    const store = createMockStore({
      getters: {
        isSuperAdmin: () => false,
        userPermissions: () => ({ 'posts_edit': true }),
        userProfile: () => ({ id: 1 }),
        userRoles: () => []
      }
    })
    const wrapper = await factory(store)

    expect(wrapper.vm.can('edit', 'posts')).toBe(true)
  })

  test('isYou returns true when id matches userProfile', async () => {
    const store = createMockStore()
    const wrapper = await factory(store)

    expect(wrapper.vm.isYou(42)).toBe(true)
    expect(wrapper.vm.isYou(99)).toBe(false)
  })

  test('hasRoles returns true when user has one of the roles', async () => {
    const store = createMockStore()
    const wrapper = await factory(store)

    expect(wrapper.vm.hasRoles(['admin'])).toBe(true)
    expect(wrapper.vm.hasRoles(['viewer'])).toBe(false)
    expect(wrapper.vm.hasRoles(['admin', 'viewer'])).toBe(true)
  })

  test('hasRoles accepts comma-separated string', async () => {
    const store = createMockStore()
    const wrapper = await factory(store)

    expect(wrapper.vm.hasRoles('admin, editor')).toBe(true)
    expect(wrapper.vm.hasRoles('viewer, guest')).toBe(false)
  })
})
