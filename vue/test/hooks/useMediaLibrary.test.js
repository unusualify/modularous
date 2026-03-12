import { describe, expect, test } from 'vitest'
import { createStore } from 'vuex'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import useMediaLibrary from '../../src/js/hooks/useMediaLibrary.js'
import mediaLibraryModule from '../../src/js/store/modules/media-library.js'

function createStoreWithMediaLibrary() {
  return createStore({
    modules: {
      mediaLibrary: mediaLibraryModule
    }
  })
}

const TestComponent = defineComponent({
  props: {
    name: { type: String, default: 'cover' },
    type: { type: String, default: 'image' }
  },
  setup(props) {
    return useMediaLibrary(props)
  },
  render: () => h('div')
})

async function factory(store, props = {}) {
  return mount(TestComponent, {
    global: { plugins: [store] },
    props: { name: 'cover', ...props }
  })
}

describe('useMediaLibrary', () => {
  test('returns openMediaLibrary function', async () => {
    const store = createStoreWithMediaLibrary()
    const wrapper = await factory(store)
    expect(typeof wrapper.vm.openMediaLibrary).toBe('function')
  })

  test('openMediaLibrary commits MEDIA_LIBRARY mutations', async () => {
    const store = createStoreWithMediaLibrary()
    const spy = vi.spyOn(store, 'commit')
    const wrapper = await factory(store, { name: 'avatar', type: 'image' })
    wrapper.vm.openMediaLibrary(1, 'avatar')
    expect(spy).toHaveBeenCalledWith(expect.any(String), 'avatar')
    expect(spy).toHaveBeenCalledWith(expect.any(String), 'image')
  })
})
