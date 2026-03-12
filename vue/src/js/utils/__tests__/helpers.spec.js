import { describe, it, expect } from 'vitest'
import {
  isString,
  isObject,
  isArray,
  isset,
  issetReturn,
  dataGet,
  snakeToHeadline,
  headline,
  extractForeignKey,
  snakeNameFromForeignKey,
  dot,
  reverseDot,
  shorten,
  formatCurrencyPrice
} from '../helpers'

describe('helpers', () => {
  describe('isString', () => {
    it('returns true for strings', () => {
      expect(isString('hello')).toBe(true)
      expect(isString('')).toBe(true)
    })
    it('returns false for non-strings', () => {
      expect(isString(123)).toBe(false)
      expect(isString({})).toBe(false)
      expect(isString([])).toBe(false)
    })
  })

  describe('isObject', () => {
    it('returns true for plain objects', () => {
      expect(isObject({})).toBe(true)
      expect(isObject({ a: 1 })).toBe(true)
    })
    it('returns false for arrays and null', () => {
      expect(isObject([])).toBe(false)
      expect(isObject(null)).toBe(false)
    })
  })

  describe('isset', () => {
    it('returns true when all args are defined', () => {
      expect(isset('a')).toBe(true)
      expect(isset(0)).toBe(true)
      expect(isset(false)).toBe(true)
    })
    it('returns false for null or undefined', () => {
      expect(isset(null)).toBe(false)
      expect(isset(undefined)).toBe(false)
    })
  })

  describe('issetReturn', () => {
    it('returns arg when truthy, else defaultValue', () => {
      expect(issetReturn('a', 'default')).toBe('a')
      expect(issetReturn(null, 'default')).toBe('default')
    })
  })

  describe('dataGet', () => {
    it('gets nested value by path', () => {
      const data = { user: { name: 'John', profile: { age: 30 } } }
      expect(dataGet(data, 'user.name')).toBe('John')
      expect(dataGet(data, 'user.profile.age')).toBe(30)
    })
    it('returns default when path not found', () => {
      expect(dataGet({}, 'missing', 'default')).toBe('default')
      expect(dataGet(null, 'path', 'fallback')).toBe('fallback')
    })
  })

  describe('snakeToHeadline', () => {
    it('converts snake_case to Title Case', () => {
      expect(snakeToHeadline('hello_world')).toBe('Hello World')
    })
  })

  describe('headline', () => {
    it('converts to headline format', () => {
      expect(headline('helloWorld')).toBe('Hello World')
    })
  })

  describe('extractForeignKey', () => {
    it('extracts foreign key from model name', () => {
      expect(extractForeignKey('packageFeature')).toBe('package_feature_id')
      expect(extractForeignKey('package_features')).toBe('package_feature_id')
    })
  })

  describe('snakeNameFromForeignKey', () => {
    it('extracts model name from foreign key', () => {
      expect(snakeNameFromForeignKey('package_feature_id')).toBe('package_feature')
    })
    it('returns false when no _id suffix', () => {
      expect(snakeNameFromForeignKey('package')).toBe(false)
    })
  })

  describe('dot', () => {
    it('flattens nested object with dot notation', () => {
      const obj = { a: { b: { c: 1 } } }
      expect(dot(obj)).toEqual({ 'a.b.c': 1 })
    })
  })

  describe('reverseDot', () => {
    it('unflattens dot-notation object', () => {
      const flat = { 'a.b.c': 1 }
      expect(reverseDot(flat)).toEqual({ a: { b: { c: 1 } } })
    })
  })

  describe('shorten', () => {
    it('truncates long strings', () => {
      expect(shorten('hello world', 5)).toBe('hello...')
      expect(shorten('hi', 10)).toBe('hi')
    })
  })

  describe('formatCurrencyPrice', () => {
    it('formats amount with symbol', () => {
      expect(formatCurrencyPrice(1234.56, '€')).toBe('€1,234.56')
    })
    it('throws for invalid amount', () => {
      expect(() => formatCurrencyPrice(NaN, '€')).toThrow()
    })
  })
})
