import { map } from 'lodash-es'

import { useLocale } from '@/hooks'
import store from '@/store'

/**
 * Active CMS content locale (user’s selected language tab).
 * Reads `store.state.language.active.value` (see `store/modules/language.js`).
 *
 * @param {import('vuex').Store} [storeInstance]
 * @returns {string|undefined}
 */
export function getActiveContentLocale (storeInstance = store) {
  return storeInstance?.state?.language?.active?.value
}

export const getTranslationLanguages = (input) => {
  try {
    const Locale = useLocale()

    return map(Locale.languages.value ?? window[import.meta.env.VUE_APP_NAME].STORE.languages.all, 'value')
  } catch (error) {
    return map(store?.state?.language?.all ?? window[import.meta.env.VUE_APP_NAME].STORE.languages.all, 'value')
  }
}

export const getTranslationLocales = () => {
  try {
    const Locale = useLocale()

    return Locale.languages.value ?? window[import.meta.env.VUE_APP_NAME].STORE.languages.all
  } catch (error) {
    return store?.state?.language?.all ?? window[import.meta.env.VUE_APP_NAME].STORE.languages.all
  }
}



export function getCurrentLocale () {
  return window[import.meta.env.VUE_APP_NAME].LOCALE
}

export function isCurrentLocale24HrFormatted () {
  return new Intl.DateTimeFormat(getCurrentLocale(), {
    hour: 'numeric'
  }).formatToParts(
    new Date(2020, 0, 1, 13)
  ).find(part => part.type === 'hour').value.length === 2
}

export function getTimeFormatForCurrentLocale () {
  if (isCurrentLocale24HrFormatted()) {
    return 'HH:mm'
  } else {
    return 'hh:mm A'
  }
}
