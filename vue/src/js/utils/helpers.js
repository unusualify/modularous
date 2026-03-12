/**
 * Modular helper functions. Prefer importing from here over window.__*
 * @deprecated window.__* - Use named imports: import { isObject, dataGet } from '@/utils/helpers'
 */
import lodash, { snakeCase } from 'lodash-es'
import pluralize from 'pluralize'

export const log = (...args) => console.log(...args)

export const isString = (value) => Object.prototype.toString.call(value) === '[object String]'
export const isBoolean = (value) => typeof value === 'boolean'
export const isNumber = (value) => !isNaN(value)
export const isObject = (value) => Object.prototype.toString.call(value) === '[object Object]'
export const isArray = (value) => Array.isArray(value)

export function isset (...args) {
  if (args.length === 0) throw new Error('Empty isset')
  for (let i = 0; i < args.length; i++) {
    if (args[i] === undefined || args[i] === null) return false
  }
  return true
}

export function issetReturn (arg, defaultValue) {
  return isset(arg) ? arg : defaultValue
}

export const getMethods = (obj) => Object.getOwnPropertyNames(obj).filter(item => typeof obj[item] === 'function')

export function globalizeMethods (input) {
  if (Array.isArray(input)) {
    input.forEach((obj) => {
      getMethods(obj).forEach((v) => {
        window[v] = obj[v]
      })
    })
  } else if (isObject(input)) {
    getMethods(input).forEach((v) => {
      window[v] = input[v]
    })
  }
}

export function functionDefinition (func) {
  return Function.prototype.toString.call(func)
}

export function convertArrayOrObject (el, key = null) {
  if (isObject(el)) {
    const object = {}
    Object.keys(el).forEach((k) => {
      object[k] = convertArrayOrObject(el[k], k)
    })
    return object
  } else if (Array.isArray(el)) {
    return el.map((item) => convertArrayOrObject(item))
  } else if (typeof el === 'function') {
    let string = functionDefinition(el)
    if (key) string = string.replace(key + '(', 'function (')
    return string
  } else if (el instanceof RegExp) {
    return el.toString()
  }
  return el
}

export const printDefinition = (variable) => JSON.stringify(convertArrayOrObject(variable))
export const shorten = (string, maxLength = 40) =>
  string.length > maxLength ? string.substring(0, maxLength) + '...' : string

export const pregQuote = (str, delimeter = '') =>
  (str + '').replace(new RegExp('[.\\\\+*?\\[\\^\\]$(){}=!<>|:\\' + '-]', 'g'), '\\$&')

export function extract (obj) {
  for (const key in obj) {
    window[key] = obj[key]
  }
}

export function dot (obj, prefix = '') {
  return Object.keys(obj).reduce((acc, key) => {
    const newKey = prefix ? `${prefix}.${key}` : key
    if (typeof obj[key] === 'object' && obj[key] !== null && !Array.isArray(obj[key])) {
      Object.assign(acc, dot(obj[key], newKey))
    } else {
      acc[newKey] = obj[key]
    }
    return acc
  }, {})
}

export function reverseDot (flatObj) {
  const result = {}
  for (const [key, value] of Object.entries(flatObj)) {
    const keys = key.split('.')
    let current = result
    for (let i = 0; i < keys.length; i++) {
      const k = keys[i]
      if (i === keys.length - 1) {
        current[k] = value
      } else {
        current[k] = current[k] || {}
        current = current[k]
      }
    }
  }
  return result
}

export const wildcardChange = (string, val, searchKey = 'id') => {
  const values = Array.isArray(val) ? val.join(',') : val
  return string.replace(/^([\w.]*)?(\*)([\w_\-.*]*)$/, `$1*${searchKey}=${values}$3`)
}

function tokenizePath (path) {
  const tokens = []
  let currentToken = ''
  let inBracket = false
  let escaped = false
  for (let i = 0; i < path.length; i++) {
    const char = path[i]
    if (escaped) {
      currentToken += char
      escaped = false
    } else if (char === '\\') {
      escaped = true
    } else if (char === '[') {
      if (currentToken) {
        tokens.push(currentToken)
        currentToken = ''
      }
      inBracket = true
      currentToken += char
    } else if (char === ']') {
      if (!inBracket) throw new Error('Mismatched brackets in path')
      currentToken += char
      tokens.push(currentToken.slice(1, -1).trim())
      currentToken = ''
      inBracket = false
    } else if (char === '.' && !inBracket) {
      if (currentToken) {
        tokens.push(currentToken)
        currentToken = ''
      }
    } else {
      currentToken += char
    }
  }
  if (currentToken) tokens.push(currentToken)
  return tokens
}

export function dataGet (data, path, defaultValue) {
  if (!data || typeof path !== 'string') return defaultValue
  const parts = tokenizePath(path)
  let current = data
  for (let index in parts) {
    const part = parts[index]
    if (current === undefined || current === null) return defaultValue
    if (typeof current === 'object') {
      const matches = part ? part.match(/^\*(.*)/) : false
      if (matches) {
        const filterMatches = matches[1] && matches[1].match(/^(\w+)=([\w\d,]+)/)
        if (filterMatches) {
          const filterKey = filterMatches[1]
          const filterValues = filterMatches[2].split(',')
          current = lodash.filter(current, (el) =>
            filterValues.includes(lodash.isNumber(el[filterKey]) ? el[filterKey].toString() : el[filterKey])
          )
        }
        if (Array.isArray(current)) {
          const _index = parseInt(index)
          return current.map((item) => dataGet(item, parts.slice(_index + 1).join('.'), defaultValue))
        }
        return Object.assign({}, current)
      }
      if (part.indexOf('[') !== -1) {
        const match = part.match(/\[([^\]]*)\]/)
        current = match ? current[match[1]] : defaultValue
      } else {
        const _tmp = current[part]
        if (!_tmp) {
          current = lodash.get(current, parts.slice(index).join('.'))
          if (current) break
        } else {
          current = _tmp
        }
      }
    } else {
      return defaultValue
    }
  }
  return current === undefined ? defaultValue : current
}

export const snakeToHeadline = (str) =>
  str.split('_').map((word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()).join(' ')

export const headline = (str) => {
  const s = snakeCase(str)
  return s.split('_').map((word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()).join(' ')
}

export const extractForeignKey = (modelName) => {
  if (typeof modelName !== 'string') throw new TypeError('modelName must be a string')
  let snakeCaseName = lodash.snakeCase(modelName)
  let singularName = pluralize.isPlural(snakeCaseName) ? pluralize.singular(snakeCaseName) : snakeCaseName
  return `${singularName}_id`
}

export const snakeNameFromForeignKey = (str) => {
  const matches = str.match(/(.*)(_id)/)
  return matches ? snakeCase(matches[1]) : false
}

export const nameFromForeignKey = (str) => {
  const s = snakeNameFromForeignKey(str)
  return s ? snakeToHeadline(s) : str
}

export const moduleName = (str) => {
  const matches = str.match(/(.*)(_id)/)
  if (matches) return snakeNameFromForeignKey(matches[1])
  return snakeCase(str)
}

export function moduleTranslationName (str, i18n) {
  const { t, te } = i18n || { t: (k) => k, te: () => false }
  let isPlural = false
  let name = str
  const snakeName = snakeNameFromForeignKey(name)
  if (snakeName) {
    name = snakeName
    str = snakeName
  }
  if (pluralize.isPlural(name)) {
    isPlural = true
    name = pluralize.singular(name)
  }
  name = snakeCase(name)
  str = snakeCase(str)
  return te(`modules.${name}`) ? t(`modules.${name}`, isPlural ? 1 : 0) : snakeToHeadline(str)
}

export const removeQueryParams = (paramsToRemove) => {
  const currentUrl = new URL(window.location.href)
  const queryObject = {}
  for (const [key, value] of currentUrl.searchParams.entries()) {
    queryObject[key] = value
  }
  paramsToRemove.forEach((param) => delete queryObject[param])
  Object.keys(queryObject).forEach((key) =>
    (queryObject[key] === undefined || queryObject[key] === null) && delete queryObject[key]
  )
  const newSearchParams = new URLSearchParams()
  Object.keys(queryObject).forEach((key) => newSearchParams.append(key, queryObject[key]))
  const newUrl = currentUrl.origin + currentUrl.pathname + (newSearchParams.toString() ? '?' + newSearchParams.toString() : '')
  window.history.replaceState({}, '', newUrl)
}

export const pluralizeStr = (str) => pluralize.plural(str)

export const pushQueryParams = (params) => {
  const url = new URL(window.location)
  const searchParams = url.searchParams
  Object.entries(params).forEach(([key, value]) => searchParams.set(key, value))
  window.history.pushState({}, '', url)
}

export const formatCurrencyPrice = (amount, symbol, preferedLocale = 'en') => {
  if (typeof amount !== 'number' || isNaN(amount)) throw new Error('Amount must be a valid number')
  const formatter = new Intl.NumberFormat(preferedLocale, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
  return `${symbol}${formatter.format(amount)}`
}

export const addParametersToUrl = (url, params) => {
  const urlInstance = new URL(url)
  Object.entries(params).forEach(([key, value]) => urlInstance.searchParams.set(key, value))
  return urlInstance.toString()
}

export const removeParametersFromUrl = (url, params) => {
  const urlInstance = new URL(url)
  const keys = Array.isArray(params) ? params : Object.keys(params || {})
  keys.forEach((key) => urlInstance.searchParams.delete(key))
  return urlInstance.toString()
}
