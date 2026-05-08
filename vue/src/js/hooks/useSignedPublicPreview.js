import { ref } from 'vue'
import { useStore } from 'vuex'
import { useI18n } from 'vue-i18n'
import api from '@/store/api/form'
import { ALERT } from '@/store/mutations/index'
import { getActiveContentLocale } from '@/utils/locale'

/**
 * Clipboard API is only available in a secure context; HTTP admin panels need a fallback.
 *
 * @param {string} text
 * @returns {Promise<boolean>}
 */
function copyTextToClipboard(text) {
  if (
    typeof navigator !== 'undefined'
    && navigator.clipboard
    && typeof navigator.clipboard.writeText === 'function'
    && typeof window !== 'undefined'
    && window.isSecureContext
  ) {
    return navigator.clipboard.writeText(text).then(() => true).catch(() => copyTextViaExecCommand(text))
  }

  return Promise.resolve(copyTextViaExecCommand(text))
}

/**
 * @param {string} text
 * @returns {boolean}
 */
function copyTextViaExecCommand(text) {
  if (typeof document === 'undefined') {
    return false
  }

  try {
    const ta = document.createElement('textarea')
    ta.value = text
    ta.setAttribute('readonly', '')
    ta.style.position = 'fixed'
    ta.style.top = '0'
    ta.style.left = '0'
    ta.style.width = '1px'
    ta.style.height = '1px'
    ta.style.opacity = '0'
    ta.setAttribute('aria-hidden', 'true')
    document.body.appendChild(ta)
    ta.focus()
    ta.select()
    const ok = document.execCommand('copy')
    document.body.removeChild(ta)

    return ok
  } catch {
    return false
  }
}

/**
 * Fetches a signed CMS public preview URL (panel session) and copies it to the clipboard.
 *
 * @see ManageUtilities::signedPublicPreviewFormPayload
 */
export function useSignedPublicPreview() {
  const store = useStore()
  const { t } = useI18n({ useScope: 'global' })
  const loading = ref(false)

  /**
   * @param {{ fetchUrl: string, expiresInMinutes?: number }|null|undefined} meta
   */
  function copyShareablePreviewLink(meta) {
    if (!meta?.fetchUrl) {
      return
    }

    const locale = getActiveContentLocale(store)
    const q = locale ? `?locale=${encodeURIComponent(locale)}` : ''
    loading.value = true

    api.get(
      `${meta.fetchUrl}${q}`,
      (resp) => {
        const url = resp?.data?.url
        if (!url || typeof url !== 'string') {
          loading.value = false

          return
        }

        copyTextToClipboard(url).then((ok) => {
          if (ok) {
            store.commit(ALERT.SET_ALERT, {
              message: t('fields.signed_preview_copied'),
              variant: 'success',
            })
          } else if (typeof window !== 'undefined' && typeof window.prompt === 'function') {
            window.prompt(
              t('fields.signed_preview_copy_prompt'),
              url
            )
          } else {
            store.commit(ALERT.SET_ALERT, {
              message: t('fields.signed_preview_copy_failed'),
              variant: 'warning',
            })
          }
          loading.value = false
        })
      },
      () => {
        loading.value = false
      }
    )
  }

  return {
    loading,
    copyShareablePreviewLink,
  }
}
