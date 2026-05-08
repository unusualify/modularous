/**
 * Collapse multiple warning strings into one message (single alert instance).
 *
 * @param {unknown[]} messages
 * @returns {string}
 */
export function joinFlashWarningMessages(messages) {
  if (!Array.isArray(messages) || messages.length === 0) {
    return ''
  }

  return messages
    .map((m) => String(m ?? '').trim())
    .filter(Boolean)
    .join('\n\n')
}
