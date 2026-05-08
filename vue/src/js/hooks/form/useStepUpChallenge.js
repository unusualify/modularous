// hooks/useStepUpChallenge.js
export default function useStepUpChallenge() {
  const isStepUpRequired = (response) => response?.status === 428 && response?.data?.step_up_required

  const openStepUpChallenge = (response, options = {}) => {
    if (!isStepUpRequired(response)) return false

    const {
      onVerified = null,
      modalProps = {},
    } = options

    window.$modalService.open('ue-step-up-challenge', {
      props: {
        stepUp: response.data.step_up ?? {},
      },
      emits: {
        verified: () => {
          if (typeof onVerified === 'function') onVerified()
        }
      },
      modalProps,
    })

    return true
  }

  return {
    isStepUpRequired,
    openStepUpChallenge,
  }
}
