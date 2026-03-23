import { describe, expect, test } from 'vitest'
import { defineComponent, h, ref } from 'vue'
import { mount } from '@vue/test-utils'
import useTableGroup, {
  useGroup,
  onlyGroupByChanged,
  normalizeGroupByConfig,
  normalizeGroupOrder
} from '../../../src/js/hooks/table/useTableGroup.js'

const TestComponent = defineComponent({
  props: {
    columns: { type: Array, default: () => [] }
  },
  setup (props) {
    const options = ref({
      itemsPerPage: 10,
      page: 1,
      sortBy: [],
      groupBy: [],
      search: ''
    })
    return { options, ...useGroup(props, options) }
  },
  render: () => h('div')
})

function mountTest (props = {}) {
  return mount(TestComponent, { props })
}

describe('useTableGroup', () => {
  test('useGroup is the same function as default export', () => {
    expect(useGroup).toBe(useTableGroup)
  })

  test('normalizeGroupOrder validates asc/desc', () => {
    expect(normalizeGroupOrder('asc')).toBe('asc')
    expect(normalizeGroupOrder('desc')).toBe('desc')
    expect(normalizeGroupOrder('invalid')).toBe('asc')
    expect(normalizeGroupOrder(undefined)).toBe('asc')
  })

  test('groupKeys only includes columns with groupable true', async () => {
    const wrapper = mountTest({
      columns: [
        { key: 'company_name', title: 'Company', groupable: true, groupOrder: 'asc' },
        { key: 'id', title: 'ID' }
      ]
    })
    expect(wrapper.vm.groupKeys).toEqual(['company_name'])
    expect(wrapper.vm.hasGroupableColumns).toBe(true)
    expect(wrapper.vm.hasGroupMenu).toBe(true)
  })

  test('groupOrder desc is applied when grouping that column', async () => {
    const wrapper = mountTest({
      columns: [
        { key: 'company_name', title: 'Company', groupable: true, groupOrder: 'desc' }
      ]
    })
    wrapper.vm.selectedGroupKey = 'company_name'
    expect(wrapper.vm.options.groupBy).toEqual([{ key: 'company_name', order: 'desc' }])
  })

  test('normalizeGroupByConfig accepts strings and objects', () => {
    expect(normalizeGroupByConfig([
      'a',
      { key: 'b', order: 'desc' },
      { key: 'b', order: 'asc' }
    ])).toEqual([
      { key: 'a', order: 'asc' },
      { key: 'b', order: 'desc' }
    ])
  })

  test('hasGroupableColumns is false when no groupable column', async () => {
    const wrapper = mountTest({
      columns: [{ key: 'id', title: 'ID' }]
    })
    expect(wrapper.vm.groupKeys).toEqual([])
    expect(wrapper.vm.hasGroupableColumns).toBe(false)
  })

  test('selectedGroupKey syncs with options.groupBy SortItem', async () => {
    const wrapper = mount(TestComponent, {
      props: {
        columns: [{ key: 'company_name', title: 'Company', groupable: true }]
      }
    })
    expect(wrapper.vm.selectedGroupKey).toBe(null)
    wrapper.vm.options.groupBy = [{ key: 'company_name', order: 'asc' }]
    await wrapper.vm.$nextTick()
    expect(wrapper.vm.selectedGroupKey).toBe('company_name')
  })

  test('setting selectedGroupKey updates options.groupBy', async () => {
    const wrapper = mountTest({
      columns: [{ key: 'company_name', title: 'Company', groupable: true }]
    })
    wrapper.vm.selectedGroupKey = 'company_name'
    expect(wrapper.vm.options.groupBy).toEqual([{ key: 'company_name', order: 'asc' }])
    wrapper.vm.selectedGroupKey = null
    expect(wrapper.vm.options.groupBy).toEqual([])
  })

  test('toggleGroupByColumn toggles same column off', async () => {
    const wrapper = mountTest({
      columns: [{ key: 'company_name', title: 'Company', groupable: true }]
    })
    wrapper.vm.toggleGroupByColumn('company_name')
    expect(wrapper.vm.options.groupBy.length).toBe(1)
    wrapper.vm.toggleGroupByColumn('company_name')
    expect(wrapper.vm.options.groupBy).toEqual([])
  })

  test('clearGroupBy empties groupBy', async () => {
    const wrapper = mountTest({
      columns: [{ key: 'company_name', title: 'Company', groupable: true }]
    })
    wrapper.vm.selectedGroupKey = 'company_name'
    wrapper.vm.clearGroupBy()
    expect(wrapper.vm.options.groupBy).toEqual([])
  })

  test('groupLabelForKey returns column title', async () => {
    const wrapper = mountTest({
      columns: [{ key: 'company_name', title: 'Company Name', groupable: true }]
    })
    expect(wrapper.vm.groupLabelForKey('company_name')).toBe('Company Name')
  })

  test('isGroupActiveForKey reflects selection', async () => {
    const wrapper = mountTest({
      columns: [{ key: 'company_name', title: 'Company', groupable: true }]
    })
    expect(wrapper.vm.isGroupActiveForKey('company_name')).toBe(false)
    wrapper.vm.selectedGroupKey = 'company_name'
    expect(wrapper.vm.isGroupActiveForKey('company_name')).toBe(true)
  })
})

describe('onlyGroupByChanged', () => {
  test('returns true when only groupBy differs', () => {
    const a = { page: 1, itemsPerPage: 10, sortBy: [], groupBy: [], search: '' }
    const b = { page: 1, itemsPerPage: 10, sortBy: [], groupBy: [{ key: 'x', order: 'asc' }], search: '' }
    expect(onlyGroupByChanged(a, b)).toBe(true)
  })

  test('returns false when page changes', () => {
    const a = { page: 1, itemsPerPage: 10, sortBy: [], groupBy: [], search: '' }
    const b = { page: 2, itemsPerPage: 10, sortBy: [], groupBy: [], search: '' }
    expect(onlyGroupByChanged(a, b)).toBe(false)
  })

  test('returns false when old is missing', () => {
    const b = { page: 1, groupBy: [] }
    expect(onlyGroupByChanged(undefined, b)).toBe(false)
  })

  test('returns true when only groupBy differs and Vuetify adds extra option keys', () => {
    const a = {
      page: 1,
      itemsPerPage: 10,
      sortBy: [],
      groupBy: [],
      search: '',
      mustSort: true
    }
    const b = {
      page: 1,
      itemsPerPage: 10,
      sortBy: [],
      groupBy: [{ key: 'status', order: 'asc' }],
      search: '',
      mustSort: true,
      groupDesc: []
    }
    expect(onlyGroupByChanged(a, b)).toBe(true)
  })
})
