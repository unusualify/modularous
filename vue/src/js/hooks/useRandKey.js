/**
 * useRandKey - Composable replacement for randKey mixin.
 * Returns a unique key based on Date.now() and random for component instances.
 */
export default function useRandKey () {
  return Date.now() + Math.floor(Math.random() * 9999)
}
