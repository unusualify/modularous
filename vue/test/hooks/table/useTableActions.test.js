import { describe, expect, test } from 'vitest'
import useTableActions, { makeTableActionsProps } from '../../../src/js/hooks/table/useTableActions.js'

describe('useTableActions', () => {
  test('returns empty object', () => {
    const result = useTableActions({ actions: [] }, { emit: () => {} })
    expect(Object.keys(result)).toHaveLength(0)
  })

  test('makeTableActionsProps defines actionsPosition and actions', () => {
    const props = makeTableActionsProps()
    expect(props.actionsPosition.default).toBe('top')
    expect(props.actions.default()).toEqual([])
  })
})
