import { describe, expect, test } from 'vitest'
import forEachNodelist from '../../src/js/utils/forEachNodelist.js'

describe('forEachNodelist', () => {
  test('iterates over array and calls callback for each element', () => {
    const arr = [1, 2, 3]
    const results = []

    forEachNodelist(arr, (item, index) => {
      results.push({ item, index })
    })

    expect(results).toEqual([
      { item: 1, index: 0 },
      { item: 2, index: 1 },
      { item: 3, index: 2 }
    ])
  })

  test('handles empty array', () => {
    const callback = vi.fn()
    forEachNodelist([], callback)
    expect(callback).not.toHaveBeenCalled()
  })

  test('passes scope to callback', () => {
    const scope = { id: 'test' }
    let receivedScope

    forEachNodelist([1], function () {
      receivedScope = this
    }, scope)

    expect(receivedScope).toBe(scope)
  })
})
