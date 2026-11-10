import { beforeEach, describe, expect, test, vi } from 'vitest'
import { flushPromises, mount } from '@vue/test-utils'
import axios from 'axios'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

import i18n from '../../src/js/config/i18n'
import HoverCard from '../../src/js/components/shared/HoverCard.vue'
import {
  resolveHoverCardValue,
  interpolateHoverCardTemplate,
  normalizeHoverCardFields,
  loadHoverCardItem,
  clearHoverCardCache
} from '../../src/js/utils/hoverCard'

vi.mock('axios', () => ({ default: { get: vi.fn() } }))

const waitForBatch = async () => {
  await new Promise((resolve) => setTimeout(resolve))
  await flushPromises()
}

const vuetify = createVuetify({ components, directives })

/** Renders activator and card inline so the menu content is assertable without teleport. */
const VMenuInline = {
  props: ['openOnHover'],
  template: '<div class="v-menu-inline" :data-hover="String(openOnHover)"><slot name="activator" :props="{}" /><slot /></div>'
}

const global = {
  plugins: [vuetify, i18n],
  stubs: {
    VMenu: VMenuInline,
    'ue-recursive-stuff': true,
    'ue-copy-text': true,
    'ue-dynamic-component-renderer': true
  }
}

const user = {
  id: 7,
  full_name: 'Jane Doe',
  email: 'jane@example.com',
  company: { name: 'Acme' },
  avatar: '/avatar.jpg'
}

const mountCard = (props = {}, slots = {}) => mount(HoverCard, { global, props: { item: user, ...props }, slots })

describe('hoverCard utils', () => {
  test('resolveHoverCardValue supports dot paths and fallbacks', () => {
    expect(resolveHoverCardValue(user, 'company.name')).toBe('Acme')
    expect(resolveHoverCardValue({ email: 'a@b.c' }, ['full_name', 'email'])).toBe('a@b.c')
    expect(resolveHoverCardValue(user, null)).toBeNull()
    expect(resolveHoverCardValue(null, 'id')).toBeNull()
  })

  test('resolveHoverCardValue renders {field} templates and falls back when blank', () => {
    expect(resolveHoverCardValue({ name: 'Jane', surname: 'Doe' }, '{name} {surname}')).toBe('Jane Doe')
    expect(resolveHoverCardValue({ name: 'Jane', surname: null }, '{name} {surname}')).toBe('Jane')
    expect(resolveHoverCardValue({ email: 'a@b.c' }, ['{name} {surname}', 'email'])).toBe('a@b.c')
  })

  test('interpolateHoverCardTemplate fills placeholders and blanks on missing values', () => {
    expect(interpolateHoverCardTemplate('/users/:id/edit', user)).toBe('/users/7/edit')
    expect(interpolateHoverCardTemplate('http://host:8080/u/:id', user)).toBe('http://host:8080/u/7')
    expect(interpolateHoverCardTemplate('/companies/:company.name', user)).toBe('/companies/Acme')
    expect(interpolateHoverCardTemplate('/users/:missing', user)).toBe('')
  })

  test('normalizeHoverCardFields accepts strings and drops invalid entries', () => {
    expect(normalizeHoverCardFields(['email', { key: 'id', label: 'ID' }, { label: 'x' }, null]))
      .toEqual([{ key: 'email' }, { key: 'id', label: 'ID' }])
  })
})

describe('loadHoverCardItem', () => {
  beforeEach(() => {
    clearHoverCardCache()
    axios.get.mockReset()
  })

  test('batches ids requested in the same tick into one ?ids= request and caches results', async () => {
    axios.get.mockResolvedValue({ data: [{ id: 1, name: 'A' }, { id: 2, name: 'B' }] })

    const results = Promise.all([
      loadHoverCardItem('/users', 1, { params: { eagers: 'company' } }),
      loadHoverCardItem('/users', '2', { params: { eagers: 'company' } }),
      loadHoverCardItem('/users', 1, { params: { eagers: 'company' } }),
      loadHoverCardItem('/users', 3, { params: { eagers: 'company' } })
    ])
    await waitForBatch()

    expect(await results).toEqual([{ id: 1, name: 'A' }, { id: 2, name: 'B' }, { id: 1, name: 'A' }, null])
    expect(axios.get).toHaveBeenCalledTimes(1)
    expect(axios.get.mock.calls[0][0]).toBe('/users')
    expect(axios.get.mock.calls[0][1].params).toEqual({ eagers: 'company', ids: '1,2,3' })

    await loadHoverCardItem('/users', 2, { params: { eagers: 'company' } })
    expect(axios.get).toHaveBeenCalledTimes(1)
  })

  test('does not re-request ids that are still in flight', async () => {
    let release
    axios.get.mockReturnValueOnce(new Promise((resolve) => { release = resolve }))
    axios.get.mockResolvedValueOnce({ data: [{ id: 2 }] })

    const first = loadHoverCardItem('/users', 1)
    await new Promise((resolve) => setTimeout(resolve))
    const second = loadHoverCardItem('/users', 2)
    await new Promise((resolve) => setTimeout(resolve))

    expect(axios.get.mock.calls[1][1].params.ids).toBe('2')
    release({ data: [{ id: 1 }] })
    expect(await first).toEqual({ id: 1 })
    expect(await second).toEqual({ id: 2 })
  })

  test('resolves null on failure and retries on the next call', async () => {
    axios.get.mockRejectedValueOnce(new Error('403'))
    const failed = loadHoverCardItem('/users', 1)
    await waitForBatch()
    expect(await failed).toBeNull()

    axios.get.mockResolvedValueOnce({ data: [{ id: 1 }] })
    const retried = loadHoverCardItem('/users', 1)
    await waitForBatch()
    expect(await retried).toEqual({ id: 1 })
    expect(axios.get).toHaveBeenCalledTimes(2)
  })
})

describe('UeHoverCard', () => {
  test('renders title in the activator and configured details in the card', () => {
    const wrapper = mountCard({
      title: ['full_name', 'email'],
      subtitle: 'email',
      avatar: 'avatar',
      fields: [{ key: 'company.name', label: 'Company', icon: 'mdi-domain' }]
    })

    expect(wrapper.find('.ue-hover-card__activator').text()).toBe('Jane Doe')
    expect(wrapper.find('.ue-hover-card__activator .v-avatar').exists()).toBe(true)
    expect(wrapper.find('.ue-hover-card').text()).toContain('jane@example.com')
    expect(wrapper.find('.ue-hover-card__field').text()).toContain('Acme')
    expect(wrapper.find('.ue-hover-card__field').text()).toContain('Company')
  })

  test('hides avatar when not configured and empty fields unless hideEmpty is false', () => {
    const wrapper = mountCard({
      title: 'full_name',
      fields: ['phone', { key: 'fax', label: 'Fax', hideEmpty: false }]
    })

    expect(wrapper.find('.v-avatar').exists()).toBe(false)
    expect(wrapper.findAll('.ue-hover-card__field')).toHaveLength(1)
  })

  test('formats email fields as mailto links', () => {
    const wrapper = mountCard({ fields: [{ key: 'email', format: 'email' }] })

    expect(wrapper.find('.ue-hover-card__field a').attributes('href')).toBe('mailto:jane@example.com')
  })

  test('renders actions with interpolated hrefs and skips unresolved ones', () => {
    const wrapper = mountCard({
      actions: [
        { label: 'Go to user', href: '/system/system-users/:id/edit' },
        { label: 'Broken', href: '/x/:missing' }
      ]
    })

    const links = wrapper.findAll('.v-card-actions a')
    expect(links).toHaveLength(1)
    expect(links[0].attributes('href')).toBe('/system/system-users/7/edit')
    expect(links[0].text()).toBe('Go to user')
  })

  test('supports slot overrides for activator and fields', () => {
    const wrapper = mountCard({ fields: ['email'] }, {
      activator: '<button class="custom-activator">open</button>',
      'field.email': '<strong class="custom-email">{{ params.value }}</strong>'
    })

    expect(wrapper.find('.custom-activator').exists()).toBe(true)
    expect(wrapper.find('.custom-email').text()).toBe('jane@example.com')
  })

  test('renders empty text without an item', () => {
    const wrapper = mountCard({ item: null, emptyText: 'n/a' })

    expect(wrapper.find('.ue-hover-card__activator').exists()).toBe(false)
    expect(wrapper.text()).toBe('n/a')
  })
})

describe('UeHoverCard with endpoint', () => {
  beforeEach(() => {
    clearHoverCardCache()
    axios.get.mockReset()
  })

  test('fetches the record by id and renders it after loading', async () => {
    axios.get.mockResolvedValue({ data: [{ id: 7, name: 'Jane', surname: 'Doe', email: 'jane@example.com' }] })

    const wrapper = mountCard({ item: 7, endpoint: '/system/system-users', title: '{name} {surname}', subtitle: 'email' })

    expect(wrapper.find('.ue-hover-card--loading').exists()).toBe(true)
    await waitForBatch()

    expect(wrapper.find('.ue-hover-card--loading').exists()).toBe(false)
    expect(wrapper.find('.ue-hover-card__activator').text()).toBe('Jane Doe')
    expect(wrapper.find('.ue-hover-card').text()).toContain('jane@example.com')
  })

  test('shows empty text when the record cannot be loaded', async () => {
    axios.get.mockRejectedValue(new Error('403'))

    const wrapper = mountCard({ item: 7, endpoint: '/system/system-users', emptyText: 'n/a' })
    await waitForBatch()

    expect(wrapper.text()).toBe('n/a')
  })

  test('uses an inline object without requesting', async () => {
    mountCard({ endpoint: '/system/system-users' })
    await waitForBatch()

    expect(axios.get).not.toHaveBeenCalled()
  })
})
