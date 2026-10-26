import { beforeEach, describe, expect, test, vi } from 'vitest'
import { flushPromises, mount } from '@vue/test-utils'
import axios from 'axios'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

import i18n from '../../src/js/config/i18n'
import HoverCard from '../../src/js/components/shared/HoverCard.vue'
import TableFormatterCell from '../../src/js/components/table/TableFormatterCell.vue'
import { clearHoverCardCache } from '../../src/js/utils/hoverCard'

vi.mock('axios', () => ({ default: { get: vi.fn() } }))

const vuetify = createVuetify({ components, directives })

const global = {
  plugins: [vuetify, i18n],
  stubs: {
    VMenu: { template: '<div><slot name="activator" :props="{}" /><slot /></div>' },
    'ue-recursive-stuff': true,
    'ue-copy-text': true,
    'ue-dynamic-component-renderer': true
  }
}

const user = { id: 7, full_name: 'Jane Doe', email: 'jane@example.com' }

const waitForBatch = async () => {
  await new Promise((resolve) => setTimeout(resolve))
  await flushPromises()
}

describe('TableFormatterCell hoverCard formatter', () => {
  beforeEach(() => {
    clearHoverCardCache()
    axios.get.mockReset()
  })

  const mountCell = (props = {}) => mount(TableFormatterCell, {
    global,
    props: {
      col: { key: 'recipient', formatter: ['hoverCard', { title: 'full_name', actions: [{ label: 'Go', href: '/u/:id' }] }] },
      item: { recipient: user },
      handleFormatter: () => ({}),
      itemAction: () => {},
      ...props
    }
  })

  test('passes formatter options and cell value to the hover card', () => {
    const card = mountCell().findComponent(HoverCard)

    expect(card.props('item')).toEqual(user)
    expect(card.props('title')).toBe('full_name')
    expect(card.props('openOnHover')).toBe(true)
  })

  test('uses only preloaded objects and never fetches from table options', async () => {
    const card = mountCell({
      col: { key: 'notifiable_id', formatter: ['hoverCard', { endpoint: '/system/system-users', params: { eagers: 'company' } }] },
      item: { notifiable_id: '10' }
    }).findComponent(HoverCard)
    await waitForBatch()

    expect(card.props('item')).toBeNull()
    expect(card.props('endpoint')).toBeNull()
    expect(axios.get).not.toHaveBeenCalled()
  })

  test('disables hover on mobile layouts', () => {
    const card = mountCell({ disableTooltip: true }).findComponent(HoverCard)

    expect(card.props('openOnHover')).toBe(false)
  })
})
