<template>
  <div class="pa-4">
    <v-breadcrumbs
      v-if="breadcrumbItems.length"
      class="px-0 pt-0 pb-2"
      density="compact"
    >
      <template v-for="(item, index) in breadcrumbItems" :key="index">
        <v-breadcrumbs-item
          :disabled="!!item.disabled"
          :class="{ 'text-primary cursor-pointer': isBreadcrumbClickable(item) }"
          @click="onBreadcrumbClick(item, $event)"
        >
          {{ item.title }}
        </v-breadcrumbs-item>
      </template>
    </v-breadcrumbs>

    <v-alert
      v-if="!useSiteSettings"
      type="warning"
      variant="tonal"
      class="mb-6"
      border="start"
    >
      {{ $t('messages.site_seo_db_disabled', 'Database-backed site SEO is disabled (MODULAROUS_CMS_SEO_ROBOTS_USE_SITE_SETTINGS=false). The editor shows the env fallback; saving is ignored until this is enabled.') }}
    </v-alert>

    <v-alert
      type="info"
      variant="tonal"
      class="mb-6"
      border="start"
    >
      {{ introText }}
    </v-alert>

    <v-card>
      <v-card-title class="text-h6">
        {{ $t('messages.site_seo_robots_title', 'Global robots.txt') }}
      </v-card-title>
      <v-card-text>
        <v-textarea
          v-model="robotsBody"
          :label="$t('messages.site_seo_robots_label', 'Body')"
          variant="outlined"
          rows="12"
          auto-grow
          :disabled="!useSiteSettings"
        />
      </v-card-text>
      <v-card-actions>
        <v-btn
          color="primary"
          :loading="saving"
          :disabled="!useSiteSettings"
          @click="save"
        >
          {{ $t('messages.save', 'Save') }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import MainLayout from '@/Pages/Layouts/MainLayout.vue'
import { useAlert, useStepUpAwareJsonPost } from '@/hooks'

const { t, te } = useI18n({ useScope: 'global' })
const { openAlert } = useAlert()
const { postJson } = useStepUpAwareJsonPost()
const inertiaPage = usePage()

const breadcrumbItems = computed(() => {
  const raw = inertiaPage.props.mainConfiguration?.navigation?.breadcrumbs
  return Array.isArray(raw) ? raw : []
})

function isBreadcrumbClickable (item) {
  return Boolean(item?.href) && !item?.disabled
}

function onBreadcrumbClick (item, e) {
  if (!isBreadcrumbClickable(item)) {
    return
  }
  e?.preventDefault()
  router.visit(item.href, { preserveScroll: true })
}

defineOptions({
  name: 'SiteSeo',
  layout: (layoutH, page) => layoutH(MainLayout, () => page),
})

const props = defineProps({
  siteSeoEndpoints: {
    type: Object,
    required: true,
  },
  globalRobotsTxt: {
    type: String,
    default: '',
  },
  useSiteSettings: {
    type: Boolean,
    default: true,
  },
})

const robotsBody = ref(props.globalRobotsTxt ?? '')
const saving = ref(false)

const introText = computed(() =>
  te('messages.site_seo_intro')
    ? t('messages.site_seo_intro')
    : 'This text is served at GET /robots.txt (when the route is enabled). Clearing the field and saving reverts to the environment default.'
)

async function save () {
  if (!props.useSiteSettings || !props.siteSeoEndpoints?.save) {
    return
  }
  saving.value = true
  try {
    const { data } = await postJson(
      props.siteSeoEndpoints.save,
      { global_robots_txt: robotsBody.value }
    )
    openAlert({
      message: data?.message ?? t('messages.saved', 'Saved'),
      variant: data?.ok === false ? 'error' : 'success',
    })
  } catch (e) {
    const msg = e.response?.data?.message ?? e.message ?? 'Request failed'
    openAlert({ message: String(msg), variant: 'error' })
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
</style>
