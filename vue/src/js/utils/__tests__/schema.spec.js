import { describe, it, expect } from 'vitest'
import {
  isViewOnlyInput,
  isFormEventInput,
  getTranslationInputsCount,
  getTranslationInputs,
  flattenGroupSchema,
  processInputs
} from '../schema'

describe('schema', () => {
  describe('isViewOnlyInput', () => {
    it('returns true when input has viewOnlyComponent', () => {
      expect(isViewOnlyInput({ viewOnlyComponent: 'VPreview' })).toBe(true)
    })
    it('returns false otherwise', () => {
      expect(isViewOnlyInput({ type: 'text' })).toBe(false)
    })
  })

  describe('isFormEventInput', () => {
    it('returns true for select with isEvent', () => {
      expect(isFormEventInput({ isEvent: true, type: 'select' })).toBe(true)
    })
    it('returns false when isEvent is false', () => {
      expect(isFormEventInput({ isEvent: false, type: 'select' })).toBe(false)
    })
  })

  describe('getTranslationInputs', () => {
    it('collects inputs with translated: true', () => {
      const inputs = {
        name: { type: 'text', translated: true },
        title: { type: 'text', translated: false }
      }
      expect(getTranslationInputs(inputs)).toHaveLength(1)
      expect(getTranslationInputs(inputs)[0]).toEqual(inputs.name)
    })
    it('recurses into wrap/group schema', () => {
      const inputs = {
        content: {
          type: 'wrap',
          schema: {
            headline: { type: 'text', translated: true }
          }
        }
      }
      const result = getTranslationInputs(inputs)
      expect(result).toHaveLength(1)
      expect(result[0]).toMatchObject({ type: 'text', translated: true })
    })
  })

  describe('getTranslationInputsCount', () => {
    it('returns count of translated inputs', () => {
      const inputs = {
        a: { translated: true },
        b: { translated: true },
        c: { translated: false }
      }
      expect(getTranslationInputsCount(inputs)).toBe(2)
    })
  })

  describe('flattenGroupSchema', () => {
    it('removes group name from keys', () => {
      const schema = { 'group.field': 'value', 'other': 'x' }
      expect(flattenGroupSchema(schema, 'group')).toEqual({ 'field': 'value', 'other': 'x' })
    })
  })

  describe('processInputs', () => {
    it('flattens wrap schemas', () => {
      const inputObj = {
        content: {
          type: 'wrap',
          schema: {
            title: { type: 'text' }
          }
        }
      }
      const result = processInputs(inputObj)
      expect(result.title).toBeDefined()
      expect(result.title.type).toBe('text')
    })
  })
})
