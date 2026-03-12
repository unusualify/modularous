import { describe, expect, test } from 'vitest'
import { createStore } from 'vuex'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import useMediaItems from '../../src/js/hooks/useMediaItems.js'
import mediaLibraryModule from '../../src/js/store/modules/media-library.js'

function createStoreWithMediaLibrary(overrides = {}) {
  const mediaState = {
    ...mediaLibraryModule.state,
    loading: [],
    ...overrides.mediaLibrary
  }
  return createStore({
    modules: { mediaLibrary: { ...mediaLibraryModule, state: mediaState } }
  })
}

const TestComponent = defineComponent({
  props: {
    selectedItems: { type: Array, default: () => [] },
    usedItems: { type: Array, default: () => [] }
  },
  emits: ['change', 'shiftChange'],
  setup(props, context) {
    return useMediaItems(props, context.emit)
  },
  render: () => h('div')
})

async function factory(store, props = {}) {
  return mount(TestComponent, {
    global: { plugins: [store] },
    props: { selectedItems: [], usedItems: [], ...props }
  })
}

describe('useMediaItems', () => {
  test('returns itemsLoading, replacingMediaIds, isSelected, isUsed, toggleSelection', async () => {
    const store = createStoreWithMediaLibrary()
    const wrapper = await factory(store)
    expect(wrapper.vm.itemsLoading).toBeDefined()
    expect(wrapper.vm.replacingMediaIds).toBeDefined()
    expect(typeof wrapper.vm.isSelected).toBe('function')
    expect(typeof wrapper.vm.isUsed).toBe('function')
    expect(typeof wrapper.vm.toggleSelection).toBe('function')
  })

  test('isSelected returns true when item in selectedItems', async () => {
    const store = createStoreWithMediaLibrary()
    const wrapper = await factory(store, {
      selectedItems: [{ id: 1, name: 'a.jpg' }]
    })
    expect(wrapper.vm.isSelected({ id: 1 })).toBe(true)
    expect(wrapper.vm.isSelected({ id: 2 })).toBe(false)
  })

  test('isUsed returns true when item in usedItems', async () => {
    const store = createStoreWithMediaLibrary()
    const wrapper = await factory(store, {
      usedItems: [{ id: 3 }]
    })
    expect(wrapper.vm.isUsed({ id: 3 })).toBe(true)
    expect(wrapper.vm.isUsed({ id: 4 })).toBe(false)
  })

  test('toggleSelection emits change', async () => {
    const store = createStoreWithMediaLibrary()
    const wrapper = await factory(store)
    wrapper.vm.toggleSelection({ id: 1 })
    expect(wrapper.emitted('change')).toEqual([[{ id: 1 }]])
  })

  test('shiftToggleSelection emits shiftChange', async () => {
    const store = createStoreWithMediaLibrary()
    const wrapper = await factory(store)
    wrapper.vm.shiftToggleSelection({ id: 2 })
    expect(wrapper.emitted('shiftChange')).toEqual([[{ id: 2 }, true]])
  })

  test('replacingMediaIds builds map from loading items with isReplacement', async () => {
    const store = createStoreWithMediaLibrary({
      mediaLibrary: {
        loading: [
          { id: 10, isReplacement: true, replacementId: 5 }
        ]
      }
    })
    const wrapper = await factory(store)
    expect(wrapper.vm.replacingMediaIds).toEqual({ 5: 10 })
  })
})
