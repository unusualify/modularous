import { describe, expect, test, vi, beforeEach } from 'vitest'
import { createStore } from 'vuex'
import { defineComponent, ref } from 'vue'
import { mount } from '@vue/test-utils'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import i18n from '../../src/js/config/i18n'
import useRepeater, { makeRepeaterProps } from '../../src/js/hooks/useRepeater.js'

const vuetify = createVuetify({ components, directives })

const mockGetModel = vi.fn((schema, item) => ({}))
const mockInvokeRuleGenerator = vi.fn((schema) => schema)

vi.mock('@/utils/getFormData.js', () => ({
  getModel: (...args) => mockGetModel(...args)
}))

vi.mock('@/hooks/useValidation.js', () => ({
  default: () => ({
    invokeRuleGenerator: (...args) => mockInvokeRuleGenerator(...args)
  })
}))

beforeEach(() => {
  vi.clearAllMocks()
  mockGetModel.mockImplementation((schema) => {
    const keys = Object.keys(schema || {})
    return keys.reduce((acc, k) => {
      acc[k] = schema[k]?.default ?? ''
      return acc
    }, {})
  })
  mockInvokeRuleGenerator.mockImplementation((s) => s)
  if (!window.__headline) {
    window.__headline = (s) => (s || '').replace(/_/g, ' ')
  }
})

function createStoreStub() {
  const store = createStore({
    state: {},
    getters: {},
    mutations: {
      __setAlert: () => {}
    }
  })
  store.commit = vi.fn(store.commit)
  return store
}

const simpleSchema = {
  name: { name: 'name', type: 'text', label: 'Name' },
  email: { name: 'email', type: 'text', label: 'Email' }
}

const TestComponent = defineComponent({
  props: {
    modelValue: { type: Array, default: () => [] },
    schema: { type: Object, default: () => ({}) },
    ...makeRepeaterProps()
  },
  emits: ['update:modelValue'],
  setup(props, context) {
    return useRepeater(props, context)
  },
  template: '<div />'
})

async function factory(store, props = {}) {
  return mount(TestComponent, {
    global: { plugins: [vuetify, i18n, store] },
    props: { schema: simpleSchema, ...props }
  })
}

describe('useRepeater', () => {
  test('makeRepeaterProps returns schema, modelValue, max, min', () => {
    const props = makeRepeaterProps()
    expect(props.schema).toBeDefined()
    expect(props.modelValue).toBeDefined()
    expect(props.max).toBeDefined()
    expect(props.min).toBeDefined()
    expect(props.max.default).toBe(-1)
    expect(props.min.default).toBe(-1)
  })

  test('returns repeaterModels, repeaterSchemas, totalRepeats, hasRepeaterModels', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, { schema: simpleSchema })
    expect(wrapper.vm.repeaterModels).toBeDefined()
    expect(wrapper.vm.repeaterSchemas).toBeDefined()
    expect(wrapper.vm.totalRepeats).toBeDefined()
    expect(wrapper.vm.hasRepeaterModels).toBeDefined()
    expect(wrapper.vm.addRepeaterBlock).toBeDefined()
    expect(wrapper.vm.deleteRepeaterBlock).toBeDefined()
    expect(wrapper.vm.duplicateRepeaterBlock).toBeDefined()
  })

  test('hasRepeaterModels false when empty schema', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, { schema: {} })
    expect(wrapper.vm.hasRepeaterModels).toBe(false)
    expect(wrapper.vm.totalRepeats).toBe(0)
  })

  test('addRepeaterBlock adds new block when addible', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, { schema: simpleSchema, max: 5 })
    const initialCount = wrapper.vm.totalRepeats
    wrapper.vm.addRepeaterBlock()
    expect(wrapper.vm.totalRepeats).toBe(initialCount + 1)
  })

  test('deleteRepeaterBlock removes block when deletable', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, {
      schema: simpleSchema,
      modelValue: [{ name: 'a', email: 'a@x.com' }, { name: 'b', email: 'b@x.com' }],
      min: 0
    })
    const initialCount = wrapper.vm.totalRepeats
    if (initialCount > 0) {
      wrapper.vm.deleteRepeaterBlock(0)
      expect(wrapper.vm.totalRepeats).toBe(initialCount - 1)
    }
  })

  test('deleteRepeaterBlock commits alert when not deletable', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, {
      schema: simpleSchema,
      modelValue: [{ name: 'a' }],
      min: 1
    })
    wrapper.vm.deleteRepeaterBlock(0)
    expect(store.commit).toHaveBeenCalledWith('__setAlert', expect.objectContaining({
      variant: 'warning',
      message: expect.stringContaining('at least')
    }))
  })

  test('addRepeaterBlock commits alert when max reached', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, {
      schema: simpleSchema,
      modelValue: [{ name: 'a' }, { name: 'b' }],
      max: 2
    })
    wrapper.vm.addRepeaterBlock()
    expect(store.commit).toHaveBeenCalledWith('__setAlert', expect.objectContaining({
      variant: 'warning',
      message: expect.stringContaining('at much')
    }))
  })

  test('headers computed from schema', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, { schema: simpleSchema })
    expect(wrapper.vm.headers).toBeDefined()
    expect(wrapper.vm.headers.length).toBeGreaterThan(0)
  })

  test('addButtonContent includes singularLabel when hasButtonLabel', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, {
      schema: simpleSchema,
      addButtonText: 'Add',
      hasButtonLabel: true,
      singularLabel: 'Item'
    })
    expect(wrapper.vm.addButtonContent).toContain('Item')
  })

  test('duplicateRepeaterBlock duplicates block when addible', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, {
      schema: simpleSchema,
      modelValue: [{ name: 'a', email: 'a@x.com' }],
      max: 5
    })
    const initialCount = wrapper.vm.totalRepeats
    wrapper.vm.duplicateRepeaterBlock(0)
    expect(wrapper.vm.totalRepeats).toBe(initialCount + 1)
  })

  test('duplicateRepeaterBlock commits alert when max reached', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, {
      schema: simpleSchema,
      modelValue: [{ name: 'a' }, { name: 'b' }],
      max: 2
    })
    wrapper.vm.duplicateRepeaterBlock(0)
    expect(store.commit).toHaveBeenCalledWith('__setAlert', expect.objectContaining({
      variant: 'warning'
    }))
  })

  test('onUpdateRepeaterModel updates model at index', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, {
      schema: simpleSchema,
      modelValue: [{ name: 'a' }]
    })
    const id = wrapper.vm.id
    const value = { [`repeater${id}[0][name]`]: 'updated' }
    wrapper.vm.onUpdateRepeaterModel(value, 0)
    expect(wrapper.vm.repeaterModels[0]).toEqual(value)
  })

  test('repeaterSchemas generated for each model', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, {
      schema: simpleSchema,
      modelValue: [{ name: 'a' }]
    })
    expect(wrapper.vm.repeaterSchemas).toBeDefined()
    expect(wrapper.vm.repeaterSchemas.length).toBe(wrapper.vm.totalRepeats)
  })
})
