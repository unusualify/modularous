<template>
  <div :class="['ue-table-pagination-footer d-flex align-center flex-wrap ga-6 px-4 py-3', `justify-${justify}`]">
    <div
      v-if="!hideItemsPerPage && itemsPerPageOptions.length > 0"
      class="ue-table-pagination-footer__per-page d-flex align-center ga-3"
    >
      <span class="text-body-small font-weight-medium">{{ itemsPerPageLabel }}</span>
      <v-select
        :model-value="itemsPerPage"
        :items="itemsPerPageOptions"
        :disabled="loading"
        item-title="title"
        item-value="value"
        variant="outlined"
        density="compact"
        rounded="lg"
        hide-details
        menu-icon="mdi-chevron-down"
        class="ue-table-pagination-footer__select flex-grow-0"
        @update:model-value="$emit('update:itemsPerPage', $event)"
      />
    </div>

    <span v-if="!hidePageInfo" class="ue-table-pagination-footer__page-info text-body-small font-weight-medium">
      {{ pageInfo }}
    </span>

    <v-pagination
      :model-value="page"
      :length="Math.max(pageCount, 1)"
      :total-visible="totalVisible"
      :disabled="loading"
      density="comfortable"
      size="small"
      variant="text"
      rounded="circle"
      show-first-last-page
      first-icon="mdi-chevron-double-left"
      last-icon="mdi-chevron-double-right"
      class="ue-table-pagination-footer__pagination flex-grow-0"
      @update:model-value="$emit('update:page', $event)"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  page: {
    type: Number,
    default: 1,
  },
  pageCount: {
    type: Number,
    default: 1,
  },
  itemsPerPage: {
    type: Number,
    default: 10,
  },
  itemsPerPageOptions: {
    type: Array,
    default: () => [],
  },
  totalVisible: {
    type: [Number, String],
    default: 5,
  },
  hideItemsPerPage: {
    type: Boolean,
    default: false,
  },
  hidePageInfo: {
    type: Boolean,
    default: false,
  },
  justify: {
    type: String,
    default: 'end',
    validator: (value) => ['start', 'center', 'end', 'space-between'].includes(value),
  },
  loading: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['update:page', 'update:itemsPerPage'])

const { t } = useI18n({ useScope: 'global' })

const itemsPerPageLabel = computed(() => t('Items per page'))

const pageInfo = computed(() => `${props.page} ${t('of')} ${Math.max(props.pageCount, 1)}`)
</script>

<style lang="sass">
  .ue-table-pagination-footer
    &__select
      --v-input-control-height: 40px
      width: 88px
      min-width: 88px

    &__pagination
      .v-pagination__list
        margin: 0

      .v-pagination__item,
      .v-pagination__first,
      .v-pagination__prev,
      .v-pagination__next,
      .v-pagination__last
        margin: 0 2px

        .v-btn
          width: 40px
          height: 40px

      .v-pagination__item--is-active .v-btn
        border: thin solid rgba(var(--v-border-color), var(--v-border-opacity))

        .v-btn__overlay
          opacity: 0
</style>
