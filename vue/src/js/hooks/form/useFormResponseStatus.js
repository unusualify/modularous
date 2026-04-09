// hooks/useFormResponseStatus.js
import useStepUpChallenge from './useStepUpChallenge'
import useResponseAlert from './useResponseAlert'
import { useAlert } from '@/hooks'

export default function useFormResponseStatus() {
  const { openStepUpChallenge } = useStepUpChallenge()
  const { open422MessageAlert } = useResponseAlert()
  const { openAlert } = useAlert()

  const statusHandlers = {
    428: ({ response, onRetry, setLoading, phase }) => {
      const modalProps = phase === 'success'
        ? { noActions: true }
        : { title: response?.data?.step_up?.title ?? 'Verification required' }

      const handled = openStepUpChallenge(response, { onVerified: onRetry, modalProps })
      if (handled && phase === 'success' && typeof setLoading === 'function') {
        setLoading(false)
      }

      return { handled }
    },
    422: ({ response }) => {
      const has422MessageAlert = open422MessageAlert(response)

      return {
        handled: has422MessageAlert,
        meta: { has422MessageAlert }
      }
    }
  }

  const handleResponseStatus = (response, context = {}) => {
    const status = response?.status
    if (!status || !Object.prototype.hasOwnProperty.call(statusHandlers, status)) {
      return { handled: false, meta: {} }
    }

    return statusHandlers[status]({ response, ...context }) ?? { handled: false, meta: {} }
  }

  const handleSuccessResponse = ({
    response,
    onRetry,
    setLoading,
    setServerValid,
    setFormErrors,
    props,
    states,
    rawSchema,
    getModel,
    resetValidation,
    VForm,
    emitSubmitted,
    callback,
    shouldUseInertia,
    router,
    redirector,
  }) => {
    const statusResult = handleResponseStatus(response, {
      phase: 'success',
      onRetry,
      setLoading,
    })
    if (statusResult.handled) return

    setLoading(false)

    if (Object.prototype.hasOwnProperty.call(response.data, 'errors')) {
      setServerValid(false)
      setFormErrors(response.data.errors)
    } else if (Object.prototype.hasOwnProperty.call(response.data, 'variant')) {
      setServerValid(false)
      openAlert({ message: response.data.message, variant: response.data.variant })
    }

    if (props.clearOnSaved) {
      states.model = getModel(rawSchema.value)
      resetValidation()
      VForm.value && VForm.value.reset()
    }

    emitSubmitted(response.data)

    let callbackFunction = callback

    if (!props.refreshOnSaved || (Object.prototype.hasOwnProperty.call(response.data, 'forceRedirect') && response.data.forceRedirect)) {
      redirector(response.data)
    } else {
      const reload = () => {
        if (shouldUseInertia.value && !props.forceRefresh) {
          router.reload({ only: ['formAttributes', ...(props.reloadOnly || [])] })
        } else {
          window.location.reload(true)
        }
      }

      callbackFunction = (data) => {
        if (callback && typeof callback === 'function') callback(data)
        reload()
      }
    }

    if (callbackFunction && typeof callbackFunction === 'function') callbackFunction(response.data)
  }

  const handleErrorResponse = ({
    response,
    onRetry,
    setLoading,
    errorCallback,
  }) => {
    setLoading(false)

    const errorStatus = handleResponseStatus(response, {
      phase: 'error',
      onRetry,
    })
    if (errorStatus.handled && response?.status === 428) return

    const has422MessageAlert = errorStatus?.meta?.has422MessageAlert === true

    if (Object.prototype.hasOwnProperty.call(response.data, 'exception')) {
      openAlert({ message: 'Your submission could not be processed.', variant: 'error' })
    } else if (!has422MessageAlert) {
      openAlert({ message: 'Your submission could not be validated, please fix and retry', variant: 'error' })
    }

    if (errorCallback && typeof errorCallback === 'function') errorCallback(response.data)
  }

  return {
    handleResponseStatus,
    handleSuccessResponse,
    handleErrorResponse,
  }
}
