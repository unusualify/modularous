import { describe, expect, test } from 'vitest'
import { createStore } from 'vuex'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import useUser from '../../src/js/hooks/useUser.js'
import userModule from '../../src/js/store/modules/user.js'
import configModule from '../../src/js/store/modules/config.js'
import ambientModule from '../../src/js/store/modules/ambient.js'

function createStoreWithUser(overrides = {}) {
  const userState = {
    timezone: 'Europe/London',
    valid_company: true,
    profile: { id: 1, show_billing_banner: false },
    authorization: {
      isSuperAdmin: false,
      isClient: false,
      permissions: { edit_post: true },
      roles: ['admin']
    },
    isGuest: false,
    ...overrides.user
  }
  return createStore({
    modules: {
      user: { ...userModule, state: userState },
      config: configModule,
      ambient: ambientModule
    }
  })
}

const TestComponent = defineComponent({
  setup() {
    return useUser()
  },
  render: () => h('div')
})

async function factory(store) {
  return mount(TestComponent, {
    global: { plugins: [store] }
  })
}

describe('useUser', () => {
  test('returns isGuest, isSuperAdmin, isClient, timezone, validCompany, showBillingBanner', async () => {
    const store = createStoreWithUser()
    const wrapper = await factory(store)
    expect(wrapper.vm.timezone).toBe('Europe/London')
    expect(wrapper.vm.validCompany).toBe(true)
    expect(wrapper.vm.showBillingBanner).toBe(false)
  })

  test('returns can, isYou, hasRoles from useAuthorization', async () => {
    const store = createStoreWithUser({
      user: { profile: { id: 5 } }
    })
    const wrapper = await factory(store)
    expect(typeof wrapper.vm.can).toBe('function')
    expect(typeof wrapper.vm.isYou).toBe('function')
    expect(typeof wrapper.vm.hasRoles).toBe('function')
  })
})
