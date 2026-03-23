import { reactive, computed, onMounted, toRefs, ref, watch } from 'vue'
import { useDisplay } from 'vuetify'
import { useStore } from 'vuex'
import { CONFIG } from '@/store/mutations'
import useNavigationLayout from './useNavigationLayout'
// import openMediaLibrary from '@/behaviors/openMediaLibrary'

export default function useSidebar () {
  const { lgAndUp, xlAndUp } = useDisplay()
  const isExpanded = ref(false)
  const activeMenuItem = ref('#profile')
  const store = useStore()
  const { persistUiPreferences } = useNavigationLayout()
  const railManual = ref(false)
  const isResizing = ref(false)

  const navigationDrawer = ref(null)
  const state = reactive({
    navigationDrawer,
    open: [],
    activeMenu: computed({
      get () {
        return activeMenuItem.value
      },
      set (val) {
        activeMenuItem.value = val
      }
    }),
    status: computed({
      get() {
        return store.state.config.sidebarStatus
      },
      set(value) {
        store.commit(CONFIG.SET_SIDEBAR, value)
      }
    }),

    // Backend merges config + user prefs; sidebarOptions fallback for guests
    options: computed(() => ({
      ...store.state.config.sidebarOptions,
      ...(store.state.config.uiPreferences?.sidebar || {})
    })),

    width: computed(() => {
      // In hidden overlay: use config default (resize only affects expanded persistent mode)
      if (state.effectiveTemporary) {
        return state.options?.width || 264
      }
      // Prefer store (shared) so Main.vue gets reactive updates during resize
      const fromStore = store.state.config.uiPreferences?.sidebar?.width
      if (fromStore !== undefined) return fromStore
      return xlAndUp.value ? 320 : (state.options?.width || 264)
    }),
    railWidth: computed(() => state.options?.railWidth ?? 56),
    hideIcons: computed(() => !state.rail && state.options?.hideIcons),
    expandHover: computed(() =>
      state.options?.expandHover ?? (state.options?.fullyHidden ? 'hidden' : 'mini')
    ),
    fullyHidden: computed(() => state.expandHover === 'hidden'),
    hoverZoneWidth: computed(() => state.options?.hoverZoneWidth ?? 12),
    isExpanded: computed(() => !state.rail),
    sidebarLocation: computed(() => state.options?.location ?? 'left'),
    rail: computed(() => {
      // In hidden mode when opened via hover (not pinned): always show expanded, not rail
      if (state.expandHover === 'hidden' && store.state.config.sidebarStatus && !state.sidebarPinned) {
        return false
      }
      const persisted = store.state.config.uiPreferences?.sidebar?.rail
      const value = persisted !== undefined ? persisted : railManual.value
      return value && lgAndUp.value
    }),
    hasRail: computed(() => state.options?.rail),
    isHoverable: computed(() => (lgAndUp.value || state.rail) && state.options?.expandOnHover),
    sidebarPinned: computed(() => store.state.config.uiPreferences?.sidebar?.pinned ?? false),
    // mini: drawer in layout (persistent) so v-main narrows. hidden+pinned: layout sidebar only on lg+.
    effectivePersistent: computed(() =>
      (state.expandHover === 'mini' && (state.isExpanded || store.state.config.sidebarStatus)) ||
      (state.expandHover === 'hidden' && (store.state.config.uiPreferences?.sidebar?.pinned ?? false) && lgAndUp.value) ||
      (state.options?.persistent ?? false)
    ),
    // In mini mode on desktop: always visible, part of layout (Vuetify layout system)
    effectivePermanent: computed(() =>
      state.expandHover === 'mini' && lgAndUp.value && store.state.config.sidebarStatus
    ),
    // Hidden: overlay when unpinned, or on mobile (pinned layout applies only on lg+)
    effectiveTemporary: computed(() =>
      state.expandHover === 'hidden' &&
        (!(store.state.config.uiPreferences?.sidebar?.pinned ?? false) || !lgAndUp.value)
    ),

    secondaryOptions: store.state.config.secondarySidebarOptions,

    profileMenu: store.state.config.profileMenu,
    socialMediaLinks: [
      [
        'mdi-twitter',
        ''
      ],
      [
        'mdi-linkedin',
        ''
      ],
      [
        'mdi-facebook',
        ''
      ],
      [
        'mdi-instagram',
        ''
      ]
    ],

  })

  const handleRailToggle = () => {
    railManual.value = !railManual.value
    const payload = { sidebar: { rail: railManual.value, status: true } }
    store.commit(CONFIG.SET_SIDEBAR, true)
    if (state.fullyHidden) {
      payload.sidebar.pinned = !railManual.value
    }
    persistUiPreferences(payload)
  }

  const handleResizeStart = (e) => {
    if (state.rail) return // Width drag only in expanded mode
    e?.preventDefault()
    isResizing.value = true
    document.body.style.cursor = 'col-resize'
    document.body.style.userSelect = 'none'
  }

  const handleResizing = (e) => {
    if (!isResizing.value || state.rail) return // Width drag only in expanded mode
    const minWidth = 256
    const maxWidth = 400
    const newWidth = e.clientX
    
    // For hidden mode: sidebar starts at 0, so clientX is the width
    // For mini mode: sidebar also starts at 0 (permanent layout), so clientX is the width
    if (newWidth >= minWidth && newWidth <= maxWidth) {
      // Update store so Main.vue (separate useSidebar instance) gets reactive width for v-main layout
      store.commit(CONFIG.SET_UI_PREFERENCES, { sidebar: { width: newWidth } })
    }
  }

  const handleSidebarLeave = () => {
    const pinned = store.state.config.uiPreferences?.sidebar?.pinned ?? false
    if (state.fullyHidden && !pinned) {
      store.commit(CONFIG.SET_SIDEBAR, false)
    }
  }

  const handleResizeEnd = () => {
    if (!isResizing.value) return
    isResizing.value = false
    document.body.style.cursor = 'default'
    document.body.style.userSelect = 'auto'
    
    const widthToPersist = store.state.config.uiPreferences?.sidebar?.width
    if (widthToPersist !== undefined) {
      persistUiPreferences({ sidebar: { width: widthToPersist } })
    }
  }

  const methods = reactive({
    handleProfile(event){
      if(event.type === 'mouseenter' && state.profileMenu?.expandOnHover) state.open.push('User')
    },
    handleMenu(title){
      state.activeMenu = `#${title}`
    },
    handleRailToggle,
    handleResizeStart,
    handleResizing,
    handleResizeEnd,
    handleSidebarLeave
  })

  watch(lgAndUp, () => {
    // state.expanded = !state.rail.value
  })
  watch(
    () => store.state.config.uiPreferences?.sidebar?.rail,
    (val) => { if (val !== undefined) railManual.value = val },
    { immediate: true }
  )
  onMounted(() => {
    const prefs = store.state.config.uiPreferences?.sidebar
    if (prefs?.rail !== undefined) railManual.value = prefs.rail
    if (prefs?.status !== undefined) {
      if (state.expandHover === 'mini' && prefs.status === false) {
        // Match config.js: open on desktop (hidden→mini), closed on mobile overlay
        store.commit(CONFIG.SET_SIDEBAR, lgAndUp.value)
      } else if (
        state.expandHover === 'hidden' &&
        prefs.status === true &&
        (!lgAndUp.value || !(prefs.pinned ?? false))
      ) {
        store.commit(CONFIG.SET_SIDEBAR, false)
      } else if (prefs.status === true && !lgAndUp.value) {
        store.commit(CONFIG.SET_SIDEBAR, false)
      } else {
        store.commit(CONFIG.SET_SIDEBAR, prefs.status)
      }
    }
    
    // Add event listeners for resize
    document.addEventListener('mousemove', handleResizing)
    document.addEventListener('mouseup', handleResizeEnd)
    
    return () => {
      document.removeEventListener('mousemove', handleResizing)
      document.removeEventListener('mouseup', handleResizeEnd)
    }
  })

  return {
    ...toRefs(state),
    ...toRefs(methods),
    railManual,
    isResizing
  }
}
