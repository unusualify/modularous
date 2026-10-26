<script setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import {
    resolveHoverCardValue,
    interpolateHoverCardTemplate,
    normalizeHoverCardFields,
    isHoverCardValueBlank
  } from '@/utils/hoverCard'
  import useHoverCardSource from '@/hooks/useHoverCardSource'

  defineOptions({
    name: 'UeHoverCard'
  })

  const props = defineProps({
    /** Record object the card describes. Only with `endpoint` may it be an id to fetch. */
    item: {
      type: [Object, String, Number],
      default: null
    },
    /**
     * Optional, for standalone use without preloaded data: a scalar `item` is fetched from
     * `endpoint?ids=…` (batched per tick, cached). Ignored when `item` is an object.
     */
    endpoint: {
      type: String,
      default: null
    },
    /** Extra query for the endpoint request, e.g. `{ eagers: 'company', appends: 'avatar' }`. */
    params: {
      type: Object,
      default: () => ({})
    },
    /** Field matching fetched records to the requested id. */
    itemKey: {
      type: String,
      default: 'id'
    },
    /** Dot path(s) for the heading; an array falls back to the next non-empty path. */
    title: {
      type: [String, Array],
      default: () => ['title', 'name']
    },
    subtitle: {
      type: [String, Array],
      default: null
    },
    /** Dot path(s) for an image URL; omit to hide the avatar. */
    avatar: {
      type: [String, Array],
      default: null
    },
    avatarIcon: {
      type: String,
      default: 'mdi-account'
    },
    /**
     * Detail rows: `'path'` or `{ key, label, icon, format, hideEmpty, dateFormat }`.
     * `format`: text (default) | email | phone | link | date | chip.
     */
    fields: {
      type: Array,
      default: () => []
    },
    /** Footer buttons: `{ label, href, icon, appendIcon, target, color, variant }`; `href` supports `:field` placeholders. */
    actions: {
      type: Array,
      default: () => []
    },
    /** Shown instead of the activator when `item` is empty. */
    emptyText: {
      type: String,
      default: '-'
    },
    openOnHover: {
      type: Boolean,
      default: true
    },
    openDelay: {
      type: [Number, String],
      default: 250
    },
    location: {
      type: String,
      default: 'bottom start'
    },
    minWidth: {
      type: [Number, String],
      default: 260
    },
    maxWidth: {
      type: [Number, String],
      default: 340
    },
    disabled: {
      type: Boolean,
      default: false
    }
  })

  const { d } = useI18n({ useScope: 'global' })

  const { resolvedItem, loading } = useHoverCardSource(props)

  const titleValue = computed(() => resolveHoverCardValue(resolvedItem.value, props.title))
  const subtitleValue = computed(() => resolveHoverCardValue(resolvedItem.value, props.subtitle))
  const avatarValue = computed(() => resolveHoverCardValue(resolvedItem.value, props.avatar))
  const hasAvatar = computed(() => !isHoverCardValueBlank(props.avatar))

  const resolvedFields = computed(() => normalizeHoverCardFields(props.fields)
    .map((field) => ({ ...field, value: resolveHoverCardValue(resolvedItem.value, field.key) }))
    .filter((field) => !(field.hideEmpty ?? true) || !isHoverCardValueBlank(field.value)))

  const resolvedActions = computed(() => (props.actions ?? [])
    .map((action) => ({ ...action, href: interpolateHoverCardTemplate(action.href, resolvedItem.value) }))
    .filter((action) => action.href))

  const displayText = (value) => Array.isArray(value) ? value.join(', ') : String(value ?? '')

  const formatDate = (value, format = 'medium') => {
    const date = new Date(value)
    return isNaN(date.getTime()) ? displayText(value) : d(date, format)
  }

  const fieldHref = (field) => {
    switch (field.format) {
      case 'email': return `mailto:${field.value}`
      case 'phone': return `tel:${field.value}`
      case 'link': return String(field.value)
      default: return null
    }
  }
</script>

<template>
  <span v-if="loading" class="ue-hover-card--loading d-inline-flex align-center ga-2">
    <v-skeleton-loader v-if="hasAvatar" type="avatar" width="28" height="28" class="bg-transparent" />
    <v-skeleton-loader type="text" width="110" class="bg-transparent" />
  </span>
  <span v-else-if="!resolvedItem" class="ue-hover-card--empty text-medium-emphasis">{{ emptyText }}</span>
  <v-menu
    v-else
    :open-on-hover="openOnHover"
    :open-delay="openDelay"
    :close-on-content-click="false"
    :location="location"
    :disabled="disabled"
  >
    <template v-slot:activator="{ props: activatorProps }">
      <slot name="activator" :props="activatorProps" :item="resolvedItem" :title="titleValue" :avatar="avatarValue">
        <div
          v-bind="activatorProps"
          class="ue-hover-card__activator d-inline-flex align-center ga-2 min-width-0 cursor-pointer"
          @click.stop
        >
          <v-avatar v-if="hasAvatar" size="28" :image="avatarValue || undefined" color="grey-lighten-2">
            <v-icon v-if="!avatarValue" :icon="avatarIcon" size="small" />
          </v-avatar>
          <span class="text-truncate">{{ displayText(titleValue) }}</span>
        </div>
      </slot>
    </template>

    <v-card class="ue-hover-card" :min-width="minWidth" :max-width="maxWidth">
      <slot name="header" :item="resolvedItem" :title="titleValue" :subtitle="subtitleValue" :avatar="avatarValue">
        <v-card-item>
          <template v-if="hasAvatar || $slots.prepend" v-slot:prepend>
            <slot name="prepend" :item="resolvedItem" :avatar="avatarValue">
              <v-avatar size="48" :image="avatarValue || undefined" color="grey-lighten-2">
                <v-icon v-if="!avatarValue" :icon="avatarIcon" />
              </v-avatar>
            </slot>
          </template>
          <v-card-title class="text-body-1 font-weight-medium">
            <slot name="title" :item="resolvedItem" :value="titleValue">{{ displayText(titleValue) }}</slot>
          </v-card-title>
          <v-card-subtitle v-if="subtitleValue || $slots.subtitle">
            <slot name="subtitle" :item="resolvedItem" :value="subtitleValue">{{ displayText(subtitleValue) }}</slot>
          </v-card-subtitle>
        </v-card-item>
      </slot>

      <slot name="fields" :item="resolvedItem" :fields="resolvedFields">
        <v-list v-if="resolvedFields.length" density="compact" class="py-0">
          <v-list-item
            v-for="field in resolvedFields"
            :key="field.key"
            :prepend-icon="field.icon"
            :subtitle="field.label"
            class="ue-hover-card__field"
          >
            <template v-slot:title>
              <slot :name="`field.${field.key}`" :item="resolvedItem" :field="field" :value="field.value">
                <v-chip v-if="field.format === 'chip'" size="small" variant="tonal">{{ displayText(field.value) }}</v-chip>
                <a
                  v-else-if="fieldHref(field)"
                  :href="fieldHref(field)"
                  :target="field.format === 'link' ? '_blank' : undefined"
                  :rel="field.format === 'link' ? 'noopener noreferrer' : undefined"
                  class="text-primary text-decoration-none"
                  @click.stop
                >{{ displayText(field.value) }}</a>
                <span v-else-if="field.format === 'date'">{{ formatDate(field.value, field.dateFormat) }}</span>
                <span v-else>{{ displayText(field.value) }}</span>
              </slot>
            </template>
          </v-list-item>
        </v-list>
      </slot>

      <slot :item="resolvedItem" />

      <slot name="actions" :item="resolvedItem" :actions="resolvedActions">
        <v-card-actions v-if="resolvedActions.length">
          <v-spacer />
          <v-btn
            v-for="(action, index) in resolvedActions"
            :key="`action-${index}`"
            :href="action.href"
            :target="action.target"
            :prepend-icon="action.icon"
            :append-icon="action.appendIcon"
            :variant="action.variant ?? 'text'"
            :color="action.color ?? 'primary'"
            size="small"
          >
            {{ action.label }}
          </v-btn>
        </v-card-actions>
      </slot>
    </v-card>
  </v-menu>
</template>
