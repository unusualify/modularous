<template>
  <ue-sidebar-drawer-content
    :status="status"
    :profile-menu-open="profileMenuOpen"
    @update:status="(v) => emit('update:status', v)"
    @update:profile-menu-open="(v) => emit('update:profileMenuOpen', v)"
    :items="items"
    :profile-menu="profileMenu"
    :mini-symbol="miniSymbol"
    :rail="rail"
    :is-hoverable="isHoverable"
    :hide-icons="hideIcons"
    :options="options"
    :width="width"
    :effective-persistent="effectivePersistent"
    :effective-permanent="effectivePermanent"
    :effective-temporary="effectiveTemporary"
    :rail-manual="railManual"
    @rail-toggle="emit('rail-toggle')"
    @activate-menu="(e) => emit('activate-menu', e)"
  >
    <template v-slot:bottom>
      <slot name="bottom" />
    </template>
  </ue-sidebar-drawer-content>
  <!-- Width drag: only in expanded persistent mode (not rail, not hidden overlay). -->
  <div v-if="!rail && status && !effectiveTemporary && $vuetify.display.lgAndUp"
    class="ue-sidebar-resize-handle"
    @mousedown="(e) => { if (!rail) emit('resize-start', e) }"
    :class="{ 'ue-sidebar-resize-active': isResizing }"
    :style="resizeHandleStyle"
  />
  <v-navigation-drawer
    v-if="options.contentDrawer?.exists"
    :width="width"
    :location="options.location"
    style="max-width: 15%"
  />
  <v-navigation-drawer
    v-if="secondaryOptions?.exists"
    :location="secondaryOptions.location"
    :width="width"
  />
</template>

<script setup>
import { computed } from 'vue'
import SidebarDrawerContent from './SidebarDrawerContent.vue'

const props = defineProps({
  items: { type: Array, required: true },
  profileMenu: { type: Array, default: () => [] },
  miniSymbol: { type: [String, Object], default: 'main-logo-dark' },
  profileMenuOpen: { type: Boolean, default: false },
  status: { type: Boolean, required: true },
  rail: { type: Boolean, required: true },
  isHoverable: { type: Boolean, required: true },
  hideIcons: { type: Boolean, required: true },
  options: { type: Object, required: true },
  width: { type: [Number, String], required: true },
  effectivePersistent: { type: Boolean, required: true },
  effectivePermanent: { type: Boolean, required: true },
  effectiveTemporary: { type: Boolean, default: false },
  railManual: { type: Boolean, required: true },
  secondaryOptions: { type: Object, default: () => ({}) },
  isResizing: { type: Boolean, default: false },
  sidebarLocation: { type: String, default: 'left' },
})

const emit = defineEmits([
  'update:status',
  'update:profileMenuOpen',
  'activate-menu',
  'rail-toggle',
  'resize-start',
])

const resizeHandleStyle = computed(() =>
  props.sidebarLocation === 'right'
    ? { right: props.width ? `${props.width}px` : 'auto' }
    : { left: props.width ? `${props.width}px` : 'auto' }
)
</script>

<style lang="sass" scoped>
.ue-sidebar-resize-handle
  position: fixed
  top: 0
  bottom: 0
  width: 8px
  cursor: col-resize
  background: transparent
  transition: background 0.2s ease
  z-index: 1102
  pointer-events: auto

  &:hover, &.ue-sidebar-resize-active
    background: rgba(var(--v-theme-primary), 0.4)
</style>
