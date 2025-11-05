<template>
  <v-combobox
    ref="VInput"
    v-model="input"
    :items="items"
    :loading="loading"
    :item-value="itemValue"
    :item-title="itemTitle"
    :multiple="multiple"
    @updatex:search="handleSearch"
    @keydown.enter="handleEnter"
    class="v-input-tag"
  >
    <template v-for="slot in Object.keys($slots)" :key="slot" v-slot:[slot]="data">
      <slot :name="slot" v-bind="data" />
    </template>
  </v-combobox>
</template>

<script>
import { ref, computed, toRefs, toRef } from 'vue'
import { useStore } from 'vuex'
import { useInput, makeInputProps, makeInputEmits, useCache } from '@/hooks'
import api from '@/store/api/form'
import { FORM, CACHE } from '@/store/mutations'

export default {
  name: 'v-input-tag',
  emits: [...makeInputEmits],
  props: {
    ...makeInputProps(),
    taggable: {
      type: String,
      default: null
    },
    items: {
      type: Array,
      default: []
    },
    multiple: {
      type: Boolean,
      default: false
    },
    endpoint: {
      type: String,
      required: null
    },
    updateEndpoint: {
      type: String,
      required: null
    },
    updatePayload: {
      type: Object,
      default: () => ({})
    },
    itemValue: {
      type: String,
      default: 'value'
    },
    itemTitle: {
      type: String,
      default: 'label'
    },
    cacheKey: {
      type: String,
      default: null
    }
  },

  setup(props, context) {
    const store = useStore()
    const Cache = useCache()
    const cacheKey = `taggable_items_${props.cacheKey ?? props.taggable}`

    const loading = ref(false)

    // if(!store.state.form.taggableItems[props.taggable]) {
    //   store.commit(FORM.SET_TAGGABLE_ITEMS, { taggable_type: props.taggable, items: props.items ?? [] })
    // }

    if(!store.getters[CACHE.HAS_CACHE](cacheKey)) {
      Cache.put(cacheKey, props.items ?? [])
    }

    const items = computed(() => {
      return Cache.get(cacheKey) ?? []
      // return store.state.form.taggableItems[props.taggable]
    })

    const addNewTag = (id, value) => {
      Cache.push(cacheKey, {
        [props.itemValue]: id,
        [props.itemTitle]: value
      })
    }

    // console.log('items', cacheKey, props.items, items.value, Cache.states)

    return {
      ...useInput(props, context),
      loading,
      items,
      addNewTag,
    }
  },

  methods: {
    async handleEnter() {

      const value = this.input

      if(Array.isArray(value)) return
      if (!value || this.items.includes(value)) return
      if (!value || this.items.find(item => item[this.itemValue] === value || item[this.itemTitle] === value)) return

      try {
        this.loading = true
        let self = this
        api.put(this.updateEndpoint, { value: value, taggable: this.taggable, ...this.updatePayload }, function(res) {
          if(res.status === 200) {
            const newTagId = res?.data?.id
            self.addNewTag(newTagId, value)
          }

        })
        return

      } catch (error) {
        console.error('Error creating new item:', error)
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style lang="sass">
  // .v-input-tag


</style>
