import { describe, it, expect } from 'vitest'
import { replaceVariablesFromHaystack } from '../notation'

describe('replaceVariablesFromHaystack', () => {
  const haystack = { id: 5, name: 'Spanish' }

  it('replaces variables inside string array items such as formEvents', () => {
    const input = replaceVariablesFromHaystack({
      name: '5_content_translation_approval',
      formEvents: ['formatSet:3.content.schema.wrap.schema.${id}$_content:rawRules:min_content.*.value'],
    }, haystack)

    expect(input.formEvents).toEqual(['formatSet:3.content.schema.wrap.schema.5_content:rawRules:min_content.*.value'])
  })

  it('keeps nested arrays as arrays', () => {
    const input = replaceVariablesFromHaystack({
      ext: [['set', 'wrap.schema.${id}$_content', 'rawRules']],
    }, haystack)

    expect(input.ext).toEqual([['set', 'wrap.schema.5_content', 'rawRules']])
  })

  it('replaces nested object strings and leaves scalars untouched', () => {
    expect(replaceVariablesFromHaystack({
      label: '${name??Translation}$ Content',
      max: 1,
      col: { class: 'item-${id}$' },
    }, haystack)).toEqual({
      label: 'Spanish Content',
      max: 1,
      col: { class: 'item-5' },
    })
  })
})
