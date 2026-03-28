<template>
  <v-card variant="outlined" class="revision-list" rounded="lg">
    <div
      class="d-flex align-center justify-space-between pa-3 cursor-pointer"
      @click="expanded = !expanded"
    >
      <div class="d-flex align-center ga-2">
        <v-icon size="small" color="primary">mdi-history</v-icon>
        <span class="text-body-2 font-weight-medium">
          Revisions
          <span v-if="revisions.length" class="text-caption text-medium-emphasis">({{ revisions.length }})</span>
        </span>
      </div>
      <div class="d-flex align-center ga-2">
        <span v-if="lastEditedText" class="text-caption text-medium-emphasis">{{ lastEditedText }}</span>
        <v-icon size="small">{{ expanded ? 'mdi-chevron-up' : 'mdi-chevron-down' }}</v-icon>
      </div>
    </div>

    <v-expand-transition>
      <div v-show="expanded" class="expand-content">
        <v-divider />

        <v-btn
          v-if="showPrevBtn"
          block
          variant="tonal"
          color="primary"
          size="small"
          rounded="0"
          prepend-icon="mdi-chevron-up"
          @click="loadPrev"
        >
          Previous
        </v-btn>

        <div ref="scrollContainer" class="revision-items" @scroll.passive="onScroll">
          <v-list density="compact" class="pa-0" bg-color="transparent">
            <v-list-item
              v-for="(revision, index) in visibleRevisions"
              :key="revision.id"
              class="px-3 py-2"
            >
              <template #prepend>
                <v-avatar size="28" color="primary" variant="tonal" class="mr-3">
                  <span class="text-caption font-weight-medium">{{ getInitials(revision.author) }}</span>
                </v-avatar>
              </template>

              <v-list-item-title class="d-flex align-center ga-2 text-body-2">
                <span class="font-weight-medium">{{ revision.author }}</span>
              </v-list-item-title>

              <v-list-item-subtitle class="text-caption mt-1">
                <v-tooltip
                  v-if="revision.source_label"
                  :text="`from ${getSourceDate(revision.source_label)}`"
                  location="top"
                >
                  <template #activator="{ props: tooltipProps }">
                    <span v-bind="tooltipProps">{{ formatDate(revision.datetime) }}</span>
                  </template>
                </v-tooltip>
                <span v-else>{{ formatDate(revision.datetime) }}</span>
              </v-list-item-subtitle>

              <template #append>
                <v-btn
                  v-if="windowStart !== 0 || index !== 0"
                  variant="text"
                  color="primary"
                  size="small"
                  class="px-2"
                  @click.stop="selectRevision(revision.id)"
                >
                  Restore
                </v-btn>
              </template>
            </v-list-item>
          </v-list>
        </div>

        <v-btn
          v-if="showNextBtn"
          block
          variant="tonal"
          color="primary"
          size="small"
          rounded="0"
          append-icon="mdi-chevron-down"
          @click="loadNext"
        >
          Next
        </v-btn>

        <v-divider />
        <div class="footer-bar px-3 py-1">
          <span class="text-caption text-medium-emphasis footer-count">{{ countText }}</span>
          <v-btn
            variant="text"
            color="primary"
            size="small"
            prepend-icon="mdi-eye-outline"
            @click.stop="emit('preview')"
          >
            Preview
          </v-btn>
        </div>
      </div>
    </v-expand-transition>
  </v-card>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'

const props = defineProps({
  revisions: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['preview', 'select'])

const expanded = ref(true)
const perPage = ref(10)
const windowStart = ref(0)
const showNextBtn = ref(false)
const showPrevBtn = ref(false)
const scrollContainer = ref(null)

const sortedRevisions = computed(() =>
  [...props.revisions].sort((a, b) => new Date(b.datetime) - new Date(a.datetime))
)

const windowEnd = computed(() =>
  Math.min(windowStart.value + perPage.value, sortedRevisions.value.length)
)

const visibleRevisions = computed(() =>
  sortedRevisions.value.slice(windowStart.value, windowEnd.value)
)

const countText = computed(() => {
  const total = sortedRevisions.value.length
  if (!total) return ''
  return `${windowStart.value + 1}-${windowEnd.value} of ${total}`
})

const lastEditedText = computed(() => {
  if (!sortedRevisions.value.length) return ''
  return timeAgo(new Date(sortedRevisions.value[0].datetime))
})

function onScroll(e) {
  const el = e.target
  showNextBtn.value = el.scrollTop + el.clientHeight >= el.scrollHeight - 40
    && windowEnd.value < sortedRevisions.value.length
  showPrevBtn.value = el.scrollTop <= 40 && windowStart.value > 0
}

async function loadNext() {
  windowStart.value = Math.min(
    windowStart.value + perPage.value,
    sortedRevisions.value.length - perPage.value
  )
  showNextBtn.value = false
  showPrevBtn.value = false
  await nextTick()
  if (scrollContainer.value) scrollContainer.value.scrollTop = 0
}

async function loadPrev() {
  windowStart.value = Math.max(0, windowStart.value - perPage.value)
  showNextBtn.value = false
  showPrevBtn.value = false
  await nextTick()
  if (scrollContainer.value) {
    scrollContainer.value.scrollTop = scrollContainer.value.scrollHeight
  }
}

watch(
  () => sortedRevisions.value[0]?.id,
  (newId, oldId) => {
    if (newId && newId !== oldId) {
      windowStart.value = 0
      showNextBtn.value = false
      showPrevBtn.value = false
      nextTick(() => {
        if (scrollContainer.value) scrollContainer.value.scrollTop = 0
      })
    }
  }
)

function selectRevision(revisionId) {
  emit('select', revisionId)
}

function getSourceDate(sourceLabel) {
  const source = props.revisions.find((r) => r.label === sourceLabel)
  return source ? formatDate(source.datetime) : sourceLabel
}

function getInitials(name) {
  if (!name) return '?'
  return name
    .split(' ')
    .map((n) => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

function formatDate(dateString) {
  const date = new Date(dateString)
  return new Intl.DateTimeFormat(undefined, {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    hour12: false,
  }).format(date)
}

function timeAgo(date) {
  const now = new Date()
  const seconds = Math.floor((now - date) / 1000)
  const intervals = [
    { label: 'year', seconds: 31536000 },
    { label: 'month', seconds: 2592000 },
    { label: 'week', seconds: 604800 },
    { label: 'day', seconds: 86400 },
    { label: 'hour', seconds: 3600 },
    { label: 'minute', seconds: 60 },
  ]
  for (const interval of intervals) {
    const count = Math.floor(seconds / interval.seconds)
    if (count >= 1) {
      return `Edited ${count} ${interval.label}${count > 1 ? 's' : ''} ago`
    }
  }
  return 'Edited just now'
}
</script>

<style scoped>
.revision-list {
  width: 100% !important;
  max-width: 100% !important;
  min-width: 0 !important;
  border-color: rgba(0, 0, 0, 0.38) !important;
  transition: border-color 0.2s ease;
}
.expand-content {
  width: 100%;
  overflow-x: hidden;
}
.revision-list:hover,
.revision-list:focus-within {
  border-color: rgba(0, 0, 0, 0.87) !important;
}
.cursor-pointer {
  cursor: pointer;
}
.revision-items {
  max-height: 320px;
  overflow-y: auto;
}
.footer-bar {
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}
.footer-count {
  position: absolute;
  right: 12px;
}
</style>
