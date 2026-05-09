/**
 * useNavigationLayout - Provides merged navigation config (PHP defaults + user preferences).
 * Supports topbar, sidebar (left/right), and bottom navigation per Vuetify 3 layout.
 */
import { computed } from 'vue'
import { useStore } from 'vuex'
import { useDisplay } from 'vuetify'
import { CONFIG } from '@/store/mutations'

export default function useNavigationLayout() {
  const store = useStore()
  const { mdAndDown, lgAndUp } = useDisplay()

  const topbarOptions = computed(() => ({
    enabled: true,
    fixed: false,
    order: 0,
    showOnMobile: true,
    showOnDesktop: true,
    ...store.state.config.topbarOptions,
    ...store.state.config.uiPreferences?.topbar
  }))

  const bottomNavOptions = computed(() => ({
    enabled: false,
    showOnMobile: true,
    showOnDesktop: false,
    ...store.state.config.bottomNavigationOptions,
    ...store.state.config.uiPreferences?.bottomNavigation
  }))

  const showTopbar = computed(() => {
    const opts = topbarOptions.value
    if (!opts.enabled) return false
    return mdAndDown.value ? opts.showOnMobile : opts.showOnDesktop
  })

  const showBottomNav = computed(() => {
    const opts = bottomNavOptions.value
    if (!opts.enabled) return false
    return mdAndDown.value ? opts.showOnMobile : opts.showOnDesktop
  })

  const persistUiPreferences = async (preferences) => {
    store.commit(CONFIG.SET_UI_PREFERENCES, preferences)

    const endpoint = store.state.config.uiPreferencesEndpoint
    if (!endpoint) return

    try {
      const { data } = await window.axios.put(endpoint, { ui_preferences: preferences })
      if (data?.ui_preferences) {
        store.commit(CONFIG.SET_UI_PREFERENCES, data.ui_preferences)
      }
    } catch (e) {
      console.warn('[Modularous] Failed to persist UI preferences:', e)
    }
  }

  return {
    topbarOptions,
    bottomNavOptions,
    showTopbar,
    showBottomNav,
    persistUiPreferences
  }
}
