import { describe, expect, test, vi, beforeEach } from 'vitest'
import { createStore } from 'vuex'
import { defineComponent, ref, reactive } from 'vue'
import { mount } from '@vue/test-utils'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import i18n from '../../src/js/config/i18n'
import useTable, { makeTableProps } from '../../src/js/hooks/useTable.js'

const vuetify = createVuetify({ components, directives })

const mockApiGet = vi.fn()
const mockSetLastParameters = vi.fn()
const mockGetQueryParameters = vi.fn(() => ({}))
const mockHeaders = ref([{ key: 'name' }, { key: 'id' }])

vi.mock('@/store/api/datatable.js', () => ({
  default: {
    get: (...args) => mockApiGet(...args),
    reorder: vi.fn()
  }
}))

vi.mock('@/hooks/table/useTableState.js', () => ({
  default: () => ({
    setLastParameters: (...args) => mockSetLastParameters(...args),
    lastParameters: {},
    getQueryParameters: (...args) => mockGetQueryParameters(...args)
  })
}))

vi.mock('@/hooks/table/useTableItem.js', () => ({
  default: (props) => {
    const editedItem = ref({})
    return {
      editedItem,
      setEditedItem: vi.fn(),
      resetEditedItem: vi.fn(),
      isDeleted: () => false,
      isSoftDeletable: () => false
    }
  }
}))

vi.mock('@/hooks/table/useTableNames.js', () => ({
  default: () => ({
    snakeName: 'posts',
    permissionName: ref('posts'),
    transNamePlural: ref('Posts'),
    transNameSingular: ref('Post'),
    deleteDialogTitle: ref('Delete'),
    deleteDialogDescription: ref('Confirm delete')
  })
}))

vi.mock('@/hooks/table/useTableFilters.js', () => ({
  default: () => ({
    search: ref(''),
    setSearchValue: vi.fn(() => true),
    setFilterSlug: vi.fn(() => true),
    setMainFilters: vi.fn(),
    clearAdvancedFilter: vi.fn(),
    activeFilterSlug: ref('all'),
    activeAdvancedFilters: ref({})
  })
}))

vi.mock('@/hooks/table/useTableHeaders.js', () => ({
  default: () => ({
    headers: mockHeaders,
    selectedHeaders: mockHeaders
  })
}))

vi.mock('@/hooks/table/useTableForms.js', () => ({
  default: () => ({
    openForm: vi.fn(),
    customFormModalActive: ref(false),
    customFormSchema: ref({}),
    customFormModel: ref({}),
    customFormAttributes: ref({})
  })
}))

vi.mock('@/hooks/table/useTableItemActions.js', () => ({
  default: () => ({
    itemAction: vi.fn(),
    itemHasAction: vi.fn(() => true),
    actionEvents: reactive({
      event: null,
      payload: null,
      reset: vi.fn()
    })
  })
}))

vi.mock('@/hooks/table/useTableModals.js', () => ({
  default: () => ({
    modals: ref({ dialog: null, show: null }),
    deleteModalActive: ref(false),
    openCustomModal: vi.fn(),
    closeCustomModal: vi.fn()
  })
}))

vi.mock('@/hooks/table/useTableActions.js', () => ({
  default: () => ({})
}))

vi.mock('@/hooks/useFormatter.js', () => ({
  default: () => ({
    formatterColumns: ref([]),
    handleFormatter: vi.fn()
  })
}))

beforeEach(() => {
  vi.clearAllMocks()
  mockGetQueryParameters.mockReturnValue({})
  Object.defineProperty(window, 'location', {
    value: new URL('https://example.com/other'),
    writable: true
  })
})

function createStoreStub() {
  return createStore({
    state: {},
    getters: { userProfile: () => ({}) },
    mutations: {},
    actions: {}
  })
}

const TestComponent = defineComponent({
  props: {
    endpoints: { type: Object, default: () => ({ index: '/api/posts' }) },
    noFetch: { type: Boolean, default: true },
    ...makeTableProps()
  },
  setup(props, context) {
    return useTable(props, context)
  },
  template: '<div />'
})

async function factory(store, props = {}) {
  return mount(TestComponent, {
    global: { plugins: [vuetify, i18n, store] },
    props: { endpoints: { index: '/api/posts' }, noFetch: true, ...props }
  })
}

describe('useTable', () => {
  test('makeTableProps returns expected props', () => {
    const props = makeTableProps()
    expect(props.items).toBeDefined()
    expect(props.endpoints).toBeDefined()
    expect(props.defaultTableOptions).toBeDefined()
    expect(props.itemsPerPageOptions).toBeDefined()
    expect(props.noFetch).toBeDefined()
    expect(props.noFetch.default).toBe(false)
  })

  test('returns form, state, and table sub-hooks when noFetch', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    expect(wrapper.vm.form).toBeDefined()
    expect(wrapper.vm.elements).toBeDefined()
    expect(wrapper.vm.options).toBeDefined()
    expect(wrapper.vm.totalNumberOfElements).toBeDefined()
    expect(wrapper.vm.totalNumberOfPages).toBeDefined()
    expect(wrapper.vm.initialize).toBeDefined()
    expect(wrapper.vm.changeOptions).toBeDefined()
    expect(wrapper.vm.snakeName).toBeDefined()
    expect(wrapper.vm.headers).toBeDefined()
  })

  test('initialize does not call api when noFetch', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    wrapper.vm.initialize()
    expect(mockApiGet).not.toHaveBeenCalled()
  })

  test('commits LANGUAGE.SET_LANGUAGES when languages prop provided', async () => {
    const store = createStoreStub()
    store.commit = vi.fn()
    await factory(store, { languages: ['en', 'tr'] })
    expect(store.commit).toHaveBeenCalledWith(expect.any(String), ['en', 'tr'])
  })

  test('changeOptions updates options when different', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    const newOptions = { page: 2, itemsPerPage: 25 }
    wrapper.vm.changeOptions(newOptions)
    expect(wrapper.vm.options.page).toBe(2)
    expect(wrapper.vm.options.itemsPerPage).toBe(25)
  })
})
