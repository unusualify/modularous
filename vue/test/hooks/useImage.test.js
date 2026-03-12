import { describe, expect, test } from 'vitest'
import { createStore } from 'vuex'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import i18n from '../../src/js/config/i18n'
import useImage, { makeImageProps } from '../../src/js/hooks/useImage.js'
import mediaLibraryModule from '../../src/js/store/modules/media-library.js'
import browserModule from '../../src/js/store/modules/browser.js'

const vuetify = createVuetify({ components, directives })

function createStoreWithMedia() {
  const mediaState = {
    ...mediaLibraryModule.state,
    selected: { cover: [] },
    isInserted: false
  }
  const browserState = {
    ...browserModule.state,
    selected: {}
  }
  return createStore({
    modules: {
      mediaLibrary: { ...mediaLibraryModule, state: mediaState },
      browser: { ...browserModule, state: browserState }
    }
  })
}

const TestComponent = defineComponent({
  props: {
    name: { type: String, required: true },
    modelValue: { default: () => [] },
    obj: { type: Object, default: () => ({}) },
    max: { type: Number, default: 1 },
    itemLabel: { type: String, default: 'Image' },
    ...makeImageProps()
  },
  emits: ['update:modelValue'],
  setup(props, context) {
    return useImage(props, context)
  },
  template: '<div />'
})

async function factory(store, props = {}) {
  return mount(TestComponent, {
    global: { plugins: [vuetify, i18n, store] },
    props: { name: 'cover', modelValue: [], ...props }
  })
}

describe('useImage', () => {
  test('returns addLabel, items, input_, deleteAll, deleteItem', async () => {
    const store = createStoreWithMedia()
    const wrapper = await factory(store)
    expect(wrapper.vm.addLabel).toBeDefined()
    expect(wrapper.vm.items).toBeDefined()
    expect(typeof wrapper.vm.deleteAll).toBe('function')
    expect(typeof wrapper.vm.deleteItem).toBe('function')
  })

  test('items returns empty when name not in selected', async () => {
    const store = createStoreWithMedia()
    const wrapper = await factory(store, { name: 'avatar' })
    expect(wrapper.vm.items).toEqual([])
  })

  test('makeImageProps defines name, max, mediaType', () => {
    const props = makeImageProps()
    expect(props.mediaType.default).toBe('image')
    expect(props.max.default).toBe(1)
    expect(props.name).toBeDefined()
  })
})
