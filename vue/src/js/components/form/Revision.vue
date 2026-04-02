<template>
  <v-list-item class="px-3 py-2">
    <template #prepend>
      <v-avatar size="28" color="primary" variant="tonal" class="mr-3">
        <span class="text-caption font-weight-medium">{{ getInitials(revision.author) }}</span>
      </v-avatar>
    </template>

    <v-list-item-title class="d-flex align-center ga-2 text-body-2">
      <span class="font-weight-medium">{{ revision.author }}</span>
    </v-list-item-title>

    <div class="text-caption text-medium-emphasis mt-1">
      <div>{{ formatDate(revision.datetime) }}</div>
      <v-tooltip v-if="revision.source_label" text="from this" location="bottom">
        <template #activator="{ props: tooltipProps }">
          <div v-bind="tooltipProps">{{ getSourceDate(revision.source_label) }}</div>
        </template>
      </v-tooltip>
    </div>

    <template #append>
      <v-btn
        v-if="showRestore"
        variant="text"
        color="primary"
        size="small"
        class="px-2"
        @click.stop="emit('restore', revision.id)"
      >
        Restore
      </v-btn>
    </template>
  </v-list-item>
</template>

<script setup>
const props = defineProps({
  revision: {
    type: Object,
    required: true,
  },
  allRevisions: {
    type: Array,
    default: () => [],
  },
  showRestore: {
    type: Boolean,
    default: true,
  },
})

const emit = defineEmits(['restore'])

function getSourceDate(sourceLabel) {
  const source = props.allRevisions.find((r) => r.label === sourceLabel)
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
</script>
