import { describe, expect, test } from 'vitest'

import {
  resolveTableTitleAttributes,
  resolveTableSubtitleAttributes,
} from '../../src/js/hooks/table/tableTitleAttributes'

describe('table title attributes', () => {
  test('inside placement keeps the compact toolbar title', () => {
    expect(resolveTableTitleAttributes('inside')).toEqual({ padding: 'a-0', type: 'subtitle-1', color: 'black' })
    expect(resolveTableSubtitleAttributes('inside')).toMatchObject({ type: 'caption', weight: 'medium', transform: 'none' })
  })

  test('outside placement uses a regular headline above the table card', () => {
    expect(resolveTableTitleAttributes('outside')).toMatchObject({ type: 'headline-small', weight: 'regular', color: 'primary-darken-3' })
    expect(resolveTableSubtitleAttributes('outside')).toMatchObject({ type: 'body-small', weight: 'regular', transform: 'none' })
  })

  test('inside placement on the card layout uses a regular uppercase card title', () => {
    expect(resolveTableTitleAttributes('inside', {}, 'card')).toMatchObject({ type: 'title-large', weight: 'regular' })
    expect(resolveTableSubtitleAttributes('inside', {}, 'card')).toMatchObject({ type: 'body-small', weight: 'regular' })
  })

  test('overrides win over placement defaults', () => {
    expect(resolveTableTitleAttributes('outside', { color: 'heading' }).color).toBe('heading')
    expect(resolveTableSubtitleAttributes('inside', { color: 'primary' }).color).toBe('primary')
  })
})
