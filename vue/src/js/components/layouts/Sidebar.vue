<template>
  <div
    v-if="fullyHidden"
    class="ue-sidebar-wrapper ue-sidebar--fully-hidden"
    :style="wrapperStyle"
    @mouseleave="handleSidebarLeave"
  >
    <div v-if="$vuetify.display.lgAndUp"
      class="ue-sidebar-hover-zone"
      :style="{ width: `${hoverZoneWidth}px` }"
      @mouseenter="handleHoverZoneEnter"
    />
    <ue-sidebar-content
      v-model:status="status"
      v-model:profile-menu-open="profileMenuOpen"
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
      :secondary-options="secondaryOptions"
      :is-resizing="isResizing"
      :sidebar-location="sidebarLocation"
      @rail-toggle="handleRailToggle"
      @activate-menu="handleMenu"
      @resize-start="handleResizeStart"
    >
      <template v-slot:bottom>
        <slot name="bottom" />
      </template>
    </ue-sidebar-content>
  </div>
  <ue-sidebar-content
    v-else
    v-model:status="status"
    v-model:profile-menu-open="profileMenuOpen"
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
    :rail-manual="railManual"
    :secondary-options="secondaryOptions"
    :is-resizing="isResizing"
    :sidebar-location="sidebarLocation"
    @rail-toggle="handleRailToggle"
    @activate-menu="handleMenu"
    @resize-start="handleResizeStart"
  >
    <template v-slot:bottom>
      <slot name="bottom" />
    </template>
  </ue-sidebar-content>
</template>

<script setup>
import { computed, ref, onMounted, provide } from 'vue'
import { useGoTo, useDisplay } from 'vuetify'
import { useStore } from 'vuex'
import { useSidebar, useSvg } from '@/hooks'
import { USER, CONFIG } from '@/store/mutations'

const props = defineProps({
  items: { type: Array, required: true },
  profileMenu: { type: Array, default: () => [] },
  rating: { type: Number, default: 0 },
  logoSymbol: { type: String, default: 'main-logo-dark' },
})

const store = useStore()
const goTo = useGoTo()
const { getLocaleSymbol } = useSvg()
const sidebar = useSidebar()
const display = useDisplay()

const {
  fullyHidden,
  hoverZoneWidth,
  status,
  rail,
  isHoverable,
  hideIcons,
  options,
  width,
  effectivePersistent,
  effectivePermanent,
  effectiveTemporary,
  railManual,
  secondaryOptions,
  isResizing,
  sidebarLocation,
  handleRailToggle,
  handleMenu,
  handleResizeStart,
  handleSidebarLeave,
} = sidebar

const profileMenuOpen = ref(false)

const miniSymbol = computed(() => getLocaleSymbol('mini-logo-dark', 'main-logo-dark'))
// Hidden mode: when open, wrapper = width + hoverZone. width is config default when overlay (effectiveTemporary), store width when pinned.
const wrapperStyle = computed(() =>
  status.value && !railManual.value && display.lgAndUp.value
    ? { width: `calc(${width.value}px + ${hoverZoneWidth.value}px)` }
    : { width: `${hoverZoneWidth.value}px` }
)

provide('activeMenu', sidebar.activeMenu)

const handleHoverZoneEnter = () => {
  if (fullyHidden.value) {
    store.commit(CONFIG.SET_SIDEBAR, true)
  }
}

onMounted(() => {
  try {
    const activeItems = window.$('.sidebar-item-active')
    const el = activeItems[activeItems.length - 1]
    goTo(el, {
      container: '.v-navigation-drawer__content',
      duration: 200,
      offset: -200,
      easing: 'easeInOutQuad',
    })
  } catch (e) {
    console.log(e)
  }
  if (props.profileMenu.length > 0) {
    profileMenuOpen.value = props.profileMenu.some((item) => item.is_active == 1)
  }
})

defineExpose({
  profileFormSubmitted(res) {
    if (typeof URLS !== 'undefined' && URLS) {
      axios.get(URLS.profileShow).then((res) => {
        store.commit(USER.SET_PROFILE_DATA, res.data)
      })
    }
  },
})
</script>

<style lang="sass" scoped>
.ue-sidebar-wrapper.ue-sidebar--fully-hidden
  position: fixed
  left: 0
  top: 0
  bottom: 0
  z-index: 1100
  transition: width 0.2s ease

.ue-sidebar-hover-zone
  position: absolute
  left: 0
  top: 0
  bottom: 0
  z-index: 1101
  cursor: pointer
  background: transparent
  transition: background 0.15s ease

  &:hover
    background: rgba(var(--v-theme-primary), 0.08)
</style>
