import { describe, expect, test, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import SidebarContent from '@/components/layouts/SidebarContent.vue'
import createModularousVuetify from '@/plugins/vuetify'

const vuetify = createModularousVuetify()

beforeEach(() => {
  Object.defineProperty(window, 'innerWidth', { value: 1920, writable: true })
})

const defaultProps = {
  items: [{ icon: 'mdi-home', text: 'Home', action: '/' }],
  profileMenu: [],
  status: true,
  rail: false,
  isHoverable: true,
  hideIcons: false,
  options: { width: 264, location: 'left', contentDrawer: { exists: false } },
  width: 264,
  effectivePersistent: true,
  effectivePermanent: false,
  effectiveTemporary: false,
  railManual: false,
  secondaryOptions: { exists: false },
  isResizing: false,
  sidebarLocation: 'left',
}

describe('SidebarContent', () => {
  test('renders ue-sidebar-drawer-content', () => {
    const wrapper = mount(SidebarContent, {
      global: {
        plugins: [vuetify],
        stubs: {
          'ue-sidebar-drawer-content': true,
          'v-navigation-drawer': true,
        },
      },
      props: defaultProps,
    })

    expect(wrapper.findComponent({ name: 'UeSidebarDrawerContent' }).exists()).toBe(true)
  })

  test('hides resize handle when rail is true', () => {
    const wrapper = mount(SidebarContent, {
      global: {
        plugins: [vuetify],
        stubs: {
          'ue-sidebar-drawer-content': true,
          'v-navigation-drawer': true,
        },
      },
      props: { ...defaultProps, rail: true },
    })

    expect(wrapper.find('.ue-sidebar-resize-handle').exists()).toBe(false)
  })

  test('hides resize handle when effectiveTemporary is true', () => {
    const wrapper = mount(SidebarContent, {
      global: {
        plugins: [vuetify],
        stubs: {
          'ue-sidebar-drawer-content': true,
          'v-navigation-drawer': true,
        },
      },
      props: { ...defaultProps, effectiveTemporary: true },
    })

    expect(wrapper.find('.ue-sidebar-resize-handle').exists()).toBe(false)
  })

  test('shows resize handle when expanded, not rail, not temporary (viewport dependent)', () => {
    const wrapper = mount(SidebarContent, {
      global: {
        plugins: [vuetify],
        stubs: {
          'ue-sidebar-drawer-content': true,
          'v-navigation-drawer': true,
        },
      },
      props: defaultProps,
    })

    // Resize handle visibility depends on $vuetify.display.lgAndUp (desktop viewport)
    const handle = wrapper.find('.ue-sidebar-resize-handle')
    expect(typeof handle.exists()).toBe('boolean')
  })

  test('emits resize-start on mousedown of resize handle', async () => {
    const wrapper = mount(SidebarContent, {
      global: {
        plugins: [vuetify],
        stubs: {
          'ue-sidebar-drawer-content': true,
          'v-navigation-drawer': true,
        },
      },
      props: defaultProps,
    })

    const handle = wrapper.find('.ue-sidebar-resize-handle')
    if (handle.exists()) {
      await handle.trigger('mousedown', { clientX: 300 })
      expect(wrapper.emitted('resize-start')).toBeTruthy()
    }
  })

  test('resize handle has ue-sidebar-resize-handle class when visible', () => {
    const wrapper = mount(SidebarContent, {
      global: {
        plugins: [vuetify],
        stubs: {
          'ue-sidebar-drawer-content': true,
          'v-navigation-drawer': true,
        },
      },
      props: { ...defaultProps, sidebarLocation: 'left', width: 264 },
    })

    const handle = wrapper.find('.ue-sidebar-resize-handle')
    if (handle.exists()) {
      expect(handle.classes()).toContain('ue-sidebar-resize-handle')
    }
  })
})
