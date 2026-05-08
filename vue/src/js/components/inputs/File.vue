<template>
  <v-input
    hideDetails="auto"
    appendIcon="mdi-close"
    :variant="boundProps.variant"
    class="v-input-file"
  >
    <template v-slot:default="defaultSlot">
      <div class="v-field v-field--active v-field--center-affix v-field--dirty v-field--variant-outlined v-locale--is-ltr">
        <div class="v-field__field" data-no-activator="">
          <div class="fileField">
            <div class="fileField__trigger" v-if="buttonOnTop && remainingItems">
              <input type="hidden" :name="name" :value="itemsIds"/>
              <v-btn type="button" @click="openMediaLibrary(remainingItems)">{{ addLabel }}</v-btn>
              <span class="fileField__note f--small">{{ note }}</span>
            </div>
            <table class="fileField__list" v-if="input.length">
              <!-- <draggable :tag="'tbody'" v-model="items" itemKey="id"> -->
              <draggable :tag="'tbody'" v-model="input" itemKey="id">
                <!-- <FileItem
                  v-for="(item, index) in items"
                  :key="item.id"
                  class="item__content"
                  :name="`${name}_${item.id}`"
                  :draggable="isDraggable"
                  :item="item"
                  @delete="deleteItem(index)">
                </FileItem> -->
                <template #item="itemSlot">
                  <FileItem
                    class="item__content"
                    :name="`${name}_${itemSlot.index}`"
                    :item-label="$t('form-labels.File')"
                    :item="input[`${itemSlot.index}`]"
                    @delete='deleteItem(itemSlot.index)'
                    >
                  </FileItem>
                </template>
              </draggable>
            </table>
            <div class="fileField__trigger" v-if="!buttonOnTop && remainingItems">
              <input type="hidden" :name="name" :value="itemsIds"/>
              <v-btn type="button" @click="openMediaLibrary(remainingItems)">{{ addLabel }}</v-btn>
              <span class="fileField__note f--small">{{ note }}</span>
            </div>
          </div>
        </div>
        <div class="v-field__outline">
          <div class="v-field__outline__start"></div>
          <div class="v-field__outline__notch">
            <label class="v-label v-field-label v-field-label--floating" aria-hidden="true" for="input-29">
              {{ boundProps.label }}
            </label>
          </div>
          <div class="v-field__outline__end"></div>
        </div>
      </div>
    </template>
  </v-input>
</template>
<script>
import { nextTick } from 'vue'
import draggable from 'vuedraggable'
import { makeFileProps, useFile, useMediaLibrary } from '@/hooks'
import { makeInputEmits } from '@/hooks'

import FileItem from '@/components/files/FileItem.vue'

export default {
  name: 'v-input-file',
  emits: [...makeInputEmits],

  components: {
    FileItem,
    draggable
  },
  props: {
    ...makeFileProps()
  },
  setup (props, context) {
    const fileApi = useFile(props, context)
    const { openMediaLibrary: baseOpen } = useMediaLibrary(props)
    const openMediaLibrary = (max, name, index) => {
      const n = name ?? props.name
      const i = typeof index === 'number' ? index : -1
      const inputVal = fileApi.input?.value ?? fileApi.input ?? []
      baseOpen(max, n, i, Array.isArray(inputVal) ? inputVal : [])
      nextTick(() => {
        // mediableActive is a ref from toRefs; assign .value so reactive state updates
        fileApi.mediableActive.value = true
      })
    }
    return {
      ...fileApi,
      openMediaLibrary
    }
  }
}
</script>

<style lang="scss" scoped>

  .v-input-file {
    padding-top: 8px;
    padding-bottom: 8px;
  }

  .fileField {
    width: 100%;
    display: block;
    border-radius: 2px;
    // border: 1px solid $color__border;
    overflow-x: hidden;
  }

  .fileField__trigger {
    padding: 10px;
    position: relative;
    border-top: 1px solid $color__border--light;

    &:first-child {
      border-top:0 none
    }
  }

  .fileField__note {
    color: $color__text--light;
    float: right;
    position: absolute;
    bottom: 18px;
    right: 15px;
    display: none;

    @include breakpoint('small+') {
      display: inline-block;
    }

    @include breakpoint('medium') {
      display: none;
    }
  }

  .fileField__list {
    overflow: hidden;
    width: 100%;
    border-collapse: collapse;
    border-spacing: 0;
  }
</style>
