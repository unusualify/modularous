import axios from 'axios'
import { useI18n } from 'vue-i18n'
import useStepUpChallenge from '@/hooks/form/useStepUpChallenge'

/**
 * Same status acceptance as `store/api/form.js` post/put plus 401 (global axios defaults).
 * Treating 428 as success lets callers open the step-up modal and retry (aligned with useFormResponseStatus).
 */
function validateStatusForPanelJson (status) {
  return (
    (status >= 200 && status < 300) ||
    status === 422 ||
    status === 401 ||
    status === 419 ||
    status === 403 ||
    status === 428
  )
}

/**
 * JSON POST for panel pages that do not use {@link useForm} (e.g. Inertia tools using axios directly).
 * Handles HTTP 428 + `step_up_required` by opening the step-up challenge modal and retrying the same request after verification.
 */
export default function useStepUpAwareJsonPost () {
  const { openStepUpChallenge } = useStepUpChallenge()
  const { t } = useI18n({ useScope: 'global' })

  /**
   * @param {string} url
   * @param {object} data
   * @param {import('axios').AxiosRequestConfig} [config]
   * @returns {Promise<import('axios').AxiosResponse>}
   */
  async function postJson (url, data, config = {}) {
    const merged = {
      ...config,
      validateStatus: validateStatusForPanelJson,
      headers: {
        'Content-Type': 'application/json',
        ...config.headers,
      },
    }

    const response = await axios.post(url, data, merged)

    if (response.status === 428 && response.data?.step_up_required) {
      return new Promise((resolve, reject) => {
        const handled = openStepUpChallenge(response, {
          onVerified: () => {
            postJson(url, data, config).then(resolve).catch(reject)
          },
          modalProps: {
            title: response?.data?.step_up?.title ?? t('messages.step_up_verification_required', 'Verification required'),
          },
        })
        if (!handled) {
          resolve(response)
        }
      })
    }

    return response
  }

  return { postJson, validateStatusForPanelJson }
}
