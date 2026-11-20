import { describe, expect, test } from 'vitest'
import { defineComponent, h, nextTick } from 'vue'
import { mount } from '@vue/test-utils'
import i18n from '../../src/js/config/i18n'
import useFilepond, { makeFilepondProps } from '../../src/js/hooks/useFilepond.js'

const TestComponent = defineComponent({
  props: {
    obj: { type: Object, default: () => ({}) },
    rules: { type: Array, default: () => [] },
    maxFiles: { type: Number, default: 2 },
    min: { type: Number },
    noRules: { type: Boolean, default: false },
    isEditing: { type: Boolean, default: false },
    editable: { type: Boolean, default: true },
    creatable: { type: Boolean, default: true },
    ...makeFilepondProps()
  },
  setup(props, context) {
    return useFilepond(props, context)
  },
  template: '<div />'
})

async function factory(props = {}) {
  return mount(TestComponent, {
    global: { plugins: [i18n] },
    props: { obj: {}, ...props }
  })
}

describe('useFilepond', () => {
  test('returns filepondRules and max', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.filepondRules).toBeDefined()
    expect(wrapper.vm.max).toBeDefined()
  })

  test('max defaults to 5 when maxFiles less than 1', async () => {
    const wrapper = await factory({ maxFiles: 0 })
    expect(wrapper.vm.max).toBe(5)
  })

  test('max equals min when min greater than max', async () => {
    const wrapper = await factory({ maxFiles: 2, min: 5 })
    expect(wrapper.vm.max).toBe(5)
  })

  test('reports upload state to the parent form while a file is loading or processing', async () => {
    const calls = []
    const wrapper = mount(TestComponent, {
      global: {
        plugins: [i18n],
        provide: { setFormInputUploading: (uid, uploading) => calls.push(uploading) },
      },
      props: { obj: {} },
    })

    // FilePond statuses: PROCESSING = 3, PROCESSING_COMPLETE = 5
    wrapper.vm.onPondFilesChange([{ status: 3 }])
    await nextTick()
    expect(wrapper.vm.pondBusy).toBe(true)

    wrapper.vm.onPondFilesChange([{ status: 5 }])
    await nextTick()
    expect(wrapper.vm.pondBusy).toBe(false)

    expect(calls).toEqual([true, false])
  })

  test('clears its upload state when unmounted mid-upload', async () => {
    const calls = []
    const wrapper = mount(TestComponent, {
      global: {
        plugins: [i18n],
        provide: { setFormInputUploading: (uid, uploading) => calls.push(uploading) },
      },
      props: { obj: {} },
    })

    wrapper.vm.onPondFilesChange([{ status: 7 }])
    await nextTick()
    wrapper.unmount()

    expect(calls.at(-1)).toBe(false)
  })

  test('makeFilepondProps defines expected props', () => {
    const props = makeFilepondProps()
    expect(props.maxFiles.default).toBe(2)
    expect(props.allowMultiple.default).toBe(false)
  })
})
