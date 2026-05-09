import { describe, expect, test } from 'vitest'
import { mount } from '@vue/test-utils'
import { createStore } from 'vuex'
import Main from '@/components/layouts/Main.vue'
import createModularousVuetify from '@/plugins/vuetify'
import mediaLibraryModule from '@/store/modules/media-library'
import i18n from '@/config/i18n'

const vuetify = createModularousVuetify()

function createTestStore() {
  return createStore({
    modules: {
      config: {
        namespaced: false,
        state: {
          sidebarStatus: true,
          sidebarOptions: { expandHover: 'mini', rail: false, width: 264 },
          topbarOptions: { enabled: true, showOnMobile: true, showOnDesktop: true },
          bottomNavigationOptions: { enabled: false },
          uiPreferences: {},
          profileMenu: [],
        },
      },
      alert: {
        namespaced: false,
        state: { dialog: false, dialogMessage: '' },
      },
      user: {
        namespaced: false,
        state: { profileDialog: false, showLoginModal: false },
      },
      mediaLibrary: mediaLibraryModule,
      ambient: {
        namespaced: false,
        state: { isHot: false },
      },
    },
    getters: {
      sidebarStatus: (state) => state.config?.sidebarStatus ?? true,
      isHot: () => false,
      userProfile: () => ({ avatar_url: '', name: '', email: '' }),
      appName: () => 'Test App',
      appEmail: () => 'test@example.com',
      isGuest: () => false,
      mediaLibraryAccessible: () => false,
    },
  })
}

describe('Main', () => {
  test('renders v-app with id inspire', () => {
    const store = createTestStore()
    const wrapper = mount(Main, {
      global: {
        plugins: [store, vuetify, i18n],
        stubs: {
          'ue-sidebar': true,
          'ue-modal-media': true,
          'ue-modal': true,
          'ue-alert': true,
          'ue-dynamic-modal': true,
          'ue-impersonate-toolbar': true,
          'ue-navigation-group': true,
        },
      },
      props: {
        headerTitle: 'Test App',
        navigation: {
          sidebar: [],
          profileMenu: [],
          sidebarBottom: [],
        },
      },
    })

    expect(wrapper.find('#inspire').exists()).toBe(true)
  })

  test('renders sidebar when hideDefaultSidebar is false', () => {
    const store = createTestStore()
    const wrapper = mount(Main, {
      global: {
        plugins: [store, vuetify, i18n],
        stubs: {
          'ue-sidebar': true,
          'ue-modal-media': true,
          'ue-modal': true,
          'ue-alert': true,
          'ue-dynamic-modal': true,
          'ue-impersonate-toolbar': true,
          'ue-navigation-group': true,
        },
      },
      props: {
        headerTitle: 'Test',
        hideDefaultSidebar: false,
        navigation: {
          sidebar: [{ icon: 'mdi-home', text: 'Home' }],
          profileMenu: [],
          sidebarBottom: [],
        },
      },
    })

    expect(wrapper.findComponent({ name: 'UeSidebar' }).exists()).toBe(true)
  })

  test('hides sidebar when hideDefaultSidebar is true', () => {
    const store = createTestStore()
    const wrapper = mount(Main, {
      global: {
        plugins: [store, vuetify, i18n],
        stubs: {
          'ue-modal-media': true,
          'ue-modal': true,
          'ue-alert': true,
          'ue-dynamic-modal': true,
        },
      },
      props: {
        headerTitle: 'Test',
        hideDefaultSidebar: true,
        navigation: { sidebar: [], profileMenu: [], sidebarBottom: [] },
      },
    })

    expect(wrapper.findComponent({ name: 'UeSidebar' }).exists()).toBe(false)
  })

  test('renders v-main with default slot', () => {
    const store = createTestStore()
    const wrapper = mount(Main, {
      global: {
        plugins: [store, vuetify, i18n],
        stubs: {
          'ue-sidebar': true,
          'ue-modal-media': true,
          'ue-modal': true,
          'ue-alert': true,
          'ue-dynamic-modal': true,
        },
      },
      props: {
        headerTitle: 'Test',
        navigation: { sidebar: [], profileMenu: [], sidebarBottom: [] },
      },
      slots: {
        default: '<div data-testid="main-content">Main content</div>',
      },
    })

    expect(wrapper.find('[data-testid="main-content"]').exists()).toBe(true)
    expect(wrapper.find('[data-testid="main-content"]').text()).toBe('Main content')
  })
})
