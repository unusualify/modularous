import { describe, expect, test } from 'vitest'
import { mount } from '@vue/test-utils'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

import i18n from '../../src/js/config/i18n'
import TablePaginationFooter from '../../src/js/components/table/TablePaginationFooter.vue'

const vuetify = createVuetify({ components, directives })

const itemsPerPageOptions = [
  { title: '10', value: 10 },
  { title: '20', value: 20 },
]

const mountFooter = (props = {}) => mount(TablePaginationFooter, {
  global: { plugins: [vuetify, i18n] },
  props: { page: 2, pageCount: 5, itemsPerPage: 10, itemsPerPageOptions, ...props },
})

describe('TablePaginationFooter', () => {
  test('renders items per page selector, page info and pagination', () => {
    const wrapper = mountFooter()

    expect(wrapper.find('.ue-table-pagination-footer__per-page').text()).toContain('Items per page')
    expect(wrapper.find('.ue-table-pagination-footer__select').exists()).toBe(true)
    expect(wrapper.find('.ue-table-pagination-footer__page-info').text()).toBe('2 of 5')
    expect(wrapper.find('.v-pagination').exists()).toBe(true)
  })

  test('hides the items per page selector when requested or without options', () => {
    expect(mountFooter({ hideItemsPerPage: true }).find('.ue-table-pagination-footer__per-page').exists()).toBe(false)
    expect(mountFooter({ itemsPerPageOptions: [] }).find('.ue-table-pagination-footer__per-page').exists()).toBe(false)
  })

  test('pagination-only variant hides page info and centers the controls', () => {
    const wrapper = mountFooter({ hideItemsPerPage: true, hidePageInfo: true, justify: 'center' })

    expect(wrapper.find('.ue-table-pagination-footer__page-info').exists()).toBe(false)
    expect(wrapper.classes()).toContain('justify-center')
    expect(mountFooter().classes()).toContain('justify-end')
  })

  test('never reports fewer than one page', () => {
    const wrapper = mountFooter({ page: 1, pageCount: -1 })

    expect(wrapper.find('.ue-table-pagination-footer__page-info').text()).toBe('1 of 1')
  })

  test('emits page and items per page updates', async () => {
    const wrapper = mountFooter()

    await wrapper.find('.v-pagination__next button').trigger('click')
    expect(wrapper.emitted('update:page')?.[0]).toEqual([3])

    wrapper.findComponent({ name: 'VSelect' }).vm.$emit('update:modelValue', 20)
    expect(wrapper.emitted('update:itemsPerPage')?.[0]).toEqual([20])
  })
})
