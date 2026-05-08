import { ref } from 'vue'
import { diffLines } from 'diff'

/**
 * @param {unknown} value
 * @returns {string}
 */
export function stringifyJsonPretty(value) {
  return JSON.stringify(value ?? {}, null, 2)
}

/**
 * Pretty-print JSON then diff by line.
 *
 * @param {unknown} left
 * @param {unknown} right
 * @returns {import('diff').Change[]}
 */
export function diffJsonLineParts(left, right) {
  return diffLines(stringifyJsonPretty(left), stringifyJsonPretty(right))
}

/**
 * CSS classes for a segment from `diff` (pair with scoped `--added` / `--removed` modifiers).
 *
 * @param {import('diff').Change} part
 * @param {string} [baseClass='json-diff-part']
 * @returns {string}
 */
export function jsonDiffPartClass(part, baseClass = 'json-diff-part') {
  if (part.added) return `${baseClass} ${baseClass}--added`
  if (part.removed) return `${baseClass} ${baseClass}--removed`
  return baseClass
}

/**
 * Line-based JSON diff for templates (`<pre>` + v-for) or further processing.
 *
 * @param {object} [options]
 * @param {string} [options.baseClass='json-diff-part'] — passed to `diffPartClass(part)`
 */
export default function useJsonDiff(options = {}) {
  const baseClass = options.baseClass ?? 'json-diff-part'
  const lineParts = ref([])

  /**
   * Diff two JSON-serializable values (objects compared after pretty-print).
   */
  function computeFromValues(left, right) {
    lineParts.value = diffJsonLineParts(left, right)
  }

  /**
   * Diff two arbitrary strings (already formatted lines).
   */
  function computeFromStrings(leftStr, rightStr) {
    lineParts.value = diffLines(leftStr, rightStr)
  }

  function clear() {
    lineParts.value = []
  }

  function diffPartClass(part) {
    return jsonDiffPartClass(part, baseClass)
  }

  return {
    lineParts,
    computeFromValues,
    computeFromStrings,
    clear,
    diffPartClass,
    baseClass,
  }
}
