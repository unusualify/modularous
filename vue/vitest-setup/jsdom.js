HTMLCanvasElement.prototype.getContext = vi.fn()

// ResizeObserver for Vuetify VApp
class ResizeObserverMock {
  observe() {}
  unobserve() {}
  disconnect() {}
}
window.ResizeObserver = ResizeObserverMock

// window.$ for Sidebar onMounted (querySelector fallback)
window.$ = window.$ || ((sel) => document.querySelectorAll(sel))

// Layout/sidebar tests need MODULARITY.STORE.config (config module reads at load time)
const APP_NAME = import.meta.env?.VUE_APP_NAME || 'MODULARITY'
if (!window[APP_NAME]) {
  window[APP_NAME] = { STORE: { config: {} } }
}
if (!window[APP_NAME].STORE?.config || Object.keys(window[APP_NAME].STORE.config).length === 0) {
  window[APP_NAME].STORE.config = {
    sidebarOptions: { expandHover: 'mini', rail: false, width: 264, railWidth: 56 },
    uiPreferences: {},
    profileMenu: [],
  }
}
