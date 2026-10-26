import axios from 'axios'
import { dataGet, isObject } from '@/utils/helpers'

const isBlank = (value) => value === undefined || value === null || value === '' ||
  (Array.isArray(value) && value.length === 0)

const TEMPLATE_TOKEN = /\{([A-Za-z_][\w.]*)\}/g

const resolvePath = (item, path) => {
  if (!path.includes('{')) {
    return dataGet(item, path)
  }
  return path
    .replace(TEMPLATE_TOKEN, (match, key) => {
      const value = dataGet(item, key)
      return isBlank(value) ? '' : String(value)
    })
    .replace(/\s+/g, ' ')
    .trim()
}

/**
 * Resolve a value from `item` by dot path; an array of paths returns the first non-blank value.
 * A path containing `{field}` tokens is a template, e.g. `'{name} {surname}'`.
 *
 * @param {Object} item
 * @param {string|string[]|null} paths
 * @returns {*}
 */
export function resolveHoverCardValue (item, paths) {
  if (!item || isBlank(paths)) {
    return null
  }
  for (const path of Array.isArray(paths) ? paths : [paths]) {
    const value = resolvePath(item, String(path))
    if (!isBlank(value)) {
      return value
    }
  }
  return null
}

/**
 * Replace `:field` placeholders (dot paths allowed) with URL-encoded values from `item`.
 * Returns '' when a placeholder has no value, so callers can hide broken links.
 *
 * @param {string} template e.g. `/system/system-users/:id/edit`
 * @param {Object} item
 * @returns {string}
 */
export function interpolateHoverCardTemplate (template, item) {
  if (!template || typeof template !== 'string') {
    return ''
  }
  let missing = false
  const result = template.replace(/:([A-Za-z_][\w.]*)/g, (match, path) => {
    const value = dataGet(item, path)
    if (isBlank(value)) {
      missing = true
      return ''
    }
    return encodeURIComponent(value)
  })
  return missing ? '' : result
}

/**
 * Normalize `fields` config: strings become `{ key }`; entries without `key` are dropped.
 *
 * @param {Array<string|Object>} fields
 * @returns {Array<{key: string, label?: string, icon?: string, format?: string, hideEmpty?: boolean}>}
 */
export function normalizeHoverCardFields (fields) {
  if (!Array.isArray(fields)) {
    return []
  }
  return fields
    .map((field) => (typeof field === 'string' ? { key: field } : field))
    .filter((field) => isObject(field) && field.key)
}

export { isBlank as isHoverCardValueBlank }

/** Max ids per `?ids=` request; larger batches are split. */
const BATCH_SIZE = 100

/** `endpoint|itemKey|params` → { items: Map<id, Promise>, queue: Map<id, resolve>, timer } */
const sources = new Map()

const getSource = (endpoint, params, itemKey) => {
  const key = `${endpoint}|${itemKey}|${JSON.stringify(params ?? {})}`
  if (!sources.has(key)) {
    sources.set(key, { endpoint, params: params ?? {}, itemKey, items: new Map(), queue: new Map(), timer: null })
  }
  return sources.get(key)
}

const requestBatch = async (source, resolvers) => {
  const ids = [...resolvers.keys()]
  try {
    const { data } = await axios.get(source.endpoint, {
      params: { ...source.params, ids: ids.join(',') },
      headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' }
    })
    const rows = Array.isArray(data) ? data : (Array.isArray(data?.data) ? data.data : [])
    const byId = new Map(rows.map((row) => [String(dataGet(row, source.itemKey)), row]))

    resolvers.forEach((resolve, id) => resolve(byId.get(id) ?? null))
  } catch (error) {
    resolvers.forEach((resolve, id) => {
      source.items.delete(id)
      resolve(null)
    })
  }
}

const flush = (source) => {
  const queued = [...source.queue]
  source.queue = new Map()
  source.timer = null
  for (let i = 0; i < queued.length; i += BATCH_SIZE) {
    requestBatch(source, new Map(queued.slice(i, i + BATCH_SIZE)))
  }
}

/**
 * Load one record from a module index endpoint (`?ids=` branch of `ManageIndexAjax`).
 * Calls made in the same tick share one request per endpoint + params; results are cached
 * for the page lifetime. Resolves `null` when the record is missing or the request fails
 * (failures are not cached, so a later call retries).
 *
 * @param {string} endpoint e.g. `/system/system-users`
 * @param {string|number} id
 * @param {{ params?: Object, itemKey?: string }} [options] `params` are sent as query (`eagers`, `appends`, `scopes`)
 * @returns {Promise<Object|null>}
 */
export function loadHoverCardItem (endpoint, id, { params = {}, itemKey = 'id' } = {}) {
  if (!endpoint || isBlank(id)) {
    return Promise.resolve(null)
  }
  const source = getSource(endpoint, params, itemKey)
  const key = String(id)

  if (!source.items.has(key)) {
    source.items.set(key, new Promise((resolve) => source.queue.set(key, resolve)))
    source.timer ??= setTimeout(() => flush(source))
  }
  return source.items.get(key)
}

/** Drop cached records (all endpoints, or one). */
export function clearHoverCardCache (endpoint = null) {
  for (const [key, source] of sources) {
    if (endpoint === null || source.endpoint === endpoint) {
      sources.delete(key)
    }
  }
}
