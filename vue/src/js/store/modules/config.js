import { CONFIG } from '../mutations'

/** Align with Vuetify `useDisplay().lgAndUp` (default lg breakpoint is 1280px). */
function isVuetifyLgAndUp () {
  if (typeof window === 'undefined' || !window.matchMedia) return true
  return window.matchMedia('(min-width: 1280px)').matches
}

const state = {
  test: window[import.meta.env.VUE_APP_NAME]?.STORE.config.test ?? false,
  isInertia: window[import.meta.env.VUE_APP_NAME]?.STORE.config.isInertia ?? false,
  useCountryBasedVatRates: window[import.meta.env.VUE_APP_NAME]?.STORE.config.useCountryBasedVatRates ?? false,

  profileMenu: window[import.meta.env.VUE_APP_NAME]?.STORE.config.profileMenu ?? [],
  sidebarStatus: (() => {
    const prefs = window[import.meta.env.VUE_APP_NAME]?.STORE.config.uiPreferences?.sidebar
    const sidebarOpts = window[import.meta.env.VUE_APP_NAME]?.STORE.config.sidebarOptions ?? {}
    const expandHover = sidebarOpts.expandHover ?? prefs?.expandHover ?? (sidebarOpts.fullyHidden ? 'hidden' : 'mini')

    if (prefs?.status !== undefined) {
      // Mini + false: desktop keeps sidebar open (hidden→mini migration); mobile stays closed
      if (expandHover === 'mini' && prefs.status === false) return isVuetifyLgAndUp()
      // Hidden + open: restore only when pinned on desktop; otherwise start closed (incl. mobile w/ pinned pref)
      if (
        expandHover === 'hidden' &&
        prefs.status === true &&
        (!isVuetifyLgAndUp() || !(prefs.pinned ?? false))
      ) {
        return false
      }
      // Mini (and other modes): persisted open from desktop must not show drawer below lg
      if (prefs.status === true && !isVuetifyLgAndUp()) return false
      return prefs.status
    }
    // Hidden overlay defaults closed; mini defaults open on lg+ only (mobile: overlay closed on load)
    if (expandHover === 'hidden') return false
    return isVuetifyLgAndUp()
  })(),
  sidebarOptions: window[import.meta.env.VUE_APP_NAME]?.STORE.config.sidebarOptions ?? [],
  secondarySidebarOptions: window[import.meta.env.VUE_APP_NAME]?.STORE.config.secondarySidebarOptions ?? [],
  topbarOptions: window[import.meta.env.VUE_APP_NAME]?.STORE.config.topbarOptions ?? { enabled: true, fixed: false, order: 0, showOnMobile: true, showOnDesktop: true },
  bottomNavigationOptions: window[import.meta.env.VUE_APP_NAME]?.STORE.config.bottomNavigationOptions ?? { enabled: false, showOnMobile: true, showOnDesktop: false },
  uiPreferences: window[import.meta.env.VUE_APP_NAME]?.STORE.config.uiPreferences ?? {},
  uiPreferencesEndpoint: window[import.meta.env.VUE_APP_NAME]?.STORE.config.uiPreferencesEndpoint ?? '',
  isRequestInProgress: false, // New state property to track async requests
  ongoingAxiosRequests: 0, // Counter for ongoing requests
}

// getters
const getters = {
  sidebarStatus: state => {
    return state.sidebarStatus
  },
  sidebarOptions: state => {
    return state.sidebarOptions
  },
  secondarySidebarOptions: state => {
    return state.secondarySidebarOptions
  },
  topbarOptions: state => state.topbarOptions,
  bottomNavigationOptions: state => state.bottomNavigationOptions,
  uiPreferences: state => state.uiPreferences,
  uiPreferencesEndpoint: state => state.uiPreferencesEndpoint,
  // profileMenu: state => {
  //   return state.profileMenu
  // },
  isRequestInProgress: state => state.isRequestInProgress, // New getter for request state
  ongoingAxiosRequests: state => state.ongoingAxiosRequests, // New getter for request state
}

const mutations = {
  [CONFIG.SIDEBAR_TOGGLE] (state) {
    state.sidebarStatus = !state.sidebarStatus; // Mutation to toggle sidebar
  },
  [CONFIG.SET_SIDEBAR] (state, status = true) {
    state.sidebarStatus = status; // Mutation to toggle sidebar
  },
  [CONFIG.SET_UI_PREFERENCES] (state, preferences = {}) {
    const merged = { ...state.uiPreferences }
    for (const key of ['sidebar', 'topbar', 'bottomNavigation']) {
      if (preferences[key] && typeof preferences[key] === 'object') {
        merged[key] = { ...(merged[key] || {}), ...preferences[key] }
      }
    }
    state.uiPreferences = merged
  },
  [CONFIG.SET_REQUEST_IN_PROGRESS] (state, isInProgress = true) {
    state.isRequestInProgress = isInProgress; // Mutation to set request state
  },
  [CONFIG.INCREASE_AXIOS_REQUEST] (state) {
    state.ongoingAxiosRequests += 1; // Mutation to set request state

    state.isRequestInProgress = state.ongoingAxiosRequests > 0
  },
  [CONFIG.DECREASE_AXIOS_REQUEST] (state) {
    state.ongoingAxiosRequests -= 1; // Mutation to set request state

    state.isRequestInProgress = state.ongoingAxiosRequests > 0
  },
}

export default {
  state,
  getters,
  mutations
}
