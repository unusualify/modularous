import { describe, expect, test } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { defineComponent, h, nextTick, ref } from 'vue'
import { VForm } from 'vuetify/components'

import Checklist from '../../src/js/components/inputs/Checklist.vue'
import UEConfig from '../../src/js/plugins/UEConfig'

function makePermissionItems (groupCount = 5, perGroup = 20) {
  const items = []
  for (let g = 0; g < groupCount; g++) {
    for (let i = 0; i < perGroup; i++) {
      items.push({
        id: g * perGroup + i + 1,
        name: `module${g}_action${i}`
      })
    }
  }
  return items
}

describe('checklist treeview (role permissions config)', () => {
  test('groups module-namespaced permissions by module and route', async () => {
    const names = [
      'system_user::user_view',
      'system_user::user_bulkDelete',
      'user::user_view',
      'announcement::announcement_create',
      'press_release::press-release-package_view',
      'dashboard'
    ]

    const wrapper = mount(Checklist, {
      global: { plugins: [UEConfig] },
      props: {
        isTreeview: true,
        closeAllGroups: true,
        modelValue: [],
        items: names.map((name, index) => ({ id: index + 1, name }))
      }
    })

    await nextTick()

    const groups = Object.fromEntries(wrapper.vm.groupedItems.map(group => [group.title ?? 'general', group]))

    expect(groups['system_user::user'].name).toBe('System User › User')
    expect(groups['system_user::user'].items.map(item => item.name)).toEqual(['View', 'Bulk Delete'])
    expect(groups['user::user'].name).toBe('User')
    expect(groups['announcement::announcement'].name).toBe('Announcement')
    expect(groups['press_release::press-release-package'].name).toBe('Press Release › Press Release Package')
    expect(groups.general.items.map(item => item.name)).toEqual(['Dashboard'])
  })

  test('treeview mounts with permission-like items and opens only first group', async () => {
    const items = makePermissionItems(5, 20)

    const wrapper = mount(Checklist, {
      global: { plugins: [UEConfig] },
      props: {
        label: 'Permissions of the role',
        isTreeview: true,
        modelValue: [],
        items
      }
    })

    await nextTick()
    await flushPromises()

    expect(wrapper.find('.v-input-checklist').exists()).toBe(true)
    expect(wrapper.vm.groupedItems.length).toBe(5)
    // >80 items: start collapsed so only group headers mount
    expect(wrapper.vm.openedGroups.length).toBe(0)

    const checkboxBtns = wrapper.findAll('.v-checkbox-btn')
    expect(checkboxBtns.length).toBe(5)
  })

  test('treeview with closeAllGroups mounts headers only', async () => {
    const items = makePermissionItems(3, 5)

    const wrapper = mount(Checklist, {
      global: { plugins: [UEConfig] },
      props: {
        label: 'Permissions of the role',
        isTreeview: true,
        closeAllGroups: true,
        modelValue: [],
        items
      }
    })

    await nextTick()

    expect(wrapper.vm.openedGroups).toEqual([])
    expect(wrapper.findAll('[data-test="checkbox"]').length).toBe(0)
    expect(wrapper.findAll('.v-checkbox-btn').length).toBe(3)
  })

  test('treeview registers only outer v-input with VForm', async () => {
    const formRef = ref(null)
    const items = makePermissionItems(3, 15)

    const Host = defineComponent({
      setup () {
        return () => h(VForm, { ref: formRef }, {
          default: () => h(Checklist, {
            label: 'Permissions of the role',
            isTreeview: true,
            modelValue: [],
            items
          })
        })
      }
    })

    const wrapper = mount(Host, {
      global: { plugins: [UEConfig] }
    })

    await nextTick()
    await flushPromises()

    expect(formRef.value.items.length).toBe(1)

    wrapper.unmount()
  })

  test('selecting a leaf permission updates modelValue', async () => {
    const items = makePermissionItems(2, 3)

    const wrapper = mount(Checklist, {
      global: { plugins: [UEConfig] },
      props: {
        label: 'Permissions of the role',
        isTreeview: true,
        modelValue: [],
        items
      }
    })

    await nextTick()

    // Small catalog opens the first group by default
    const leaf = wrapper.findAll('[data-test="checkbox"] input[type="checkbox"]')[0]
    expect(leaf.exists()).toBe(true)
    await leaf.setValue()

    const emitted = wrapper.emitted('update:modelValue')
    expect(emitted).toBeTruthy()
    expect(emitted[0][0]).toEqual([items[0].id])
  })
})
