import { describe, expect, test } from 'vitest'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import useSelect, { makeSelectProps } from '../../src/js/hooks/utils/useSelect.js'

const TestComponent = defineComponent({
  props: {
    ...makeSelectProps()
  },
  setup(props, context) {
    return useSelect(props, context)
  },
  render: () => h('div')
})

async function factory(props = {}) {
  return mount(TestComponent, {
    props: { items: [], ...props }
  })
}

describe('useSelect', () => {
  test('returns empty object from useSelect', () => {
    const props = { items: [] }
    const context = { emit: () => {} }
    const result = useSelect(props, context)
    expect(Object.keys(result)).toHaveLength(0)
  })

  test('makeSelectProps defines itemValue, itemTitle, multiple, items', () => {
    const props = makeSelectProps()
    expect(props.itemValue.default).toBe('id')
    expect(props.itemTitle.default).toBe('name')
    expect(props.multiple.default).toBe(false)
    expect(props.items.default()).toEqual([])
  })
})
