/**
 * useFormBase - Backward-compatible alias for useFormBaseLogic.
 * Adapts (props, context) to (props, { emit }, slots) for useFormBaseLogic.
 */
import useFormBaseLogic from './useFormBaseLogic'

export default function useFormBase(props, context) {
  const { emit } = context || {}
  const slots = context?.slots ?? {}
  return useFormBaseLogic(props, { emit }, slots)
}
