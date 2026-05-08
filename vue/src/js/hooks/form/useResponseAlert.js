// hooks/useResponseAlert.js
import useAlert from '@/hooks/useAlert'

export default function useResponseAlert() {
  const { openAlert } = useAlert()

  const open422MessageAlert = (response, variant = 'error') => {
    const message = response?.data?.message
    variant = response?.data?.variant ?? variant

    if (response?.status !== 422 || typeof message !== 'string' || message.trim() === '') return false

    openAlert({ message, variant })
    return true
  }

  return {
    open422MessageAlert,
  }
}
