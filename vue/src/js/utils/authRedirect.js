import store from '@/store'

/**
 * Full-page login URL (Ziggy, blade STORE, Vuex).
 */
export function resolveLoginUrl () {
  if (typeof window !== 'undefined' && typeof window.route === 'function') {
    const candidates = ['admin.login.form', 'login.form', 'admin.login', 'login']
    for (const routeName of candidates) {
      try {
        const u = window.route(routeName)
        if (u) {
          return u
        }
      } catch {
        /* try next */
      }
    }
  }

  const ns = import.meta.env.VUE_APP_NAME
  if (typeof window !== 'undefined' && ns && window[ns]?.STORE?.user?.loginRoute) {
    return window[ns].STORE.user.loginRoute
  }

  if (typeof window !== 'undefined' && window.MODULARITY?.STORE?.user?.loginRoute) {
    return window.MODULARITY.STORE.user.loginRoute
  }

  const fromStore = store.state.user?.loginRoute
  if (fromStore) {
    return fromStore
  }

  return ''
}

/**
 * 401 JSON from AuthenticateMiddleware (`login_url`, `redirect`).
 *
 * @returns {boolean} true if navigation was triggered
 */
export function redirectFromUnauthorizedPayload (data) {
  if (data?.redirect === true && data?.login_url) {
    window.location.assign(data.login_url)

    return true
  }

  return false
}

export function redirectToLoginFullPage () {
  const url = resolveLoginUrl()
  if (url) {
    window.location.assign(url)
  }
}
