<script setup>
import { watch, ref, computed, nextTick } from 'vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  active: {
    type: Boolean,
    default: false
  },
  users: {
    type: Array,
    default () {
      return []
    }
  },
  impersonated: {
    type: Boolean,
    default: false
  },
  route: {
    type: String,
    default: '/users/impersonate/:id'
  },
  stopRoute: {
    type: String,
    default: '/users/impersonate/stop'
  },
  fetchEndpoint: {
    type: String,
    default: null
  },
  itemTitle: {
    type: String,
    default: 'name'
  },
  itemValue: {
    type: String,
    default: 'id'
  },
  density: {
    type: String,
    default: 'comfortable'
  },
  variant: {
    type: String,
    default: 'outlined'
  }
})
const emit = defineEmits(['update:modelValue'])

const selected = ref(null)

const show = computed({
  get () {
    return props.modelValue
  },
  set (value) {
    emit('update:modelValue', value)
  }
})

watch(selected, function (newValue, oldValue) {
  if(newValue) {
    nextTick(() => {
      window.location.href = impersonateRoute.value
    })
  }
})

const impersonateRoute = computed(() => {
  return props.route.replace(':id', selected.value)
})


</script>
<template>
  <v-bottom-sheet v-model="show">
    <template v-slot:activator="{ props }">
      <v-list-item
          v-bind="props"
          class="font-weight-bold mt-2"
          :ripple="false"
          :append="false"
          prepend-icon="mdi-account-switch"
          title="Impersonate Manager"
          >
      </v-list-item>

    </template>

    <div class="pa-3 bg-primary">
      <v-btn v-if="impersonated" color="red" :href="stopRoute">
          Stop Impersonating
      </v-btn>
      <!-- <v-select v-if="!impersonated"
        class="mt-5"
        v-model="selected"
        :items="users"
        variant="outlined"
        density="compact"
        item-title="name"
        item-value="id"
        clearable
        >
        <template v-slot:item="{ props: itemProps, item }">
          <v-list-item v-bind="itemProps" :title="`${item.raw.name} (${item.raw.company_name})`" :subtitle="item.raw.email"></v-list-item>
        </template>
      </v-select> -->
      <v-input-browser v-if="!impersonated"
        class="mt-5"
        v-model="selected"

        :endpoint="fetchEndpoint"
        :variant="variant"
        :density="density"
        :item-title="itemTitle"
        :item-value="itemValue"
        >
        <template v-slot:item="{ item }">
          <v-list-item :title="`${item.raw.name} (${item.raw.company_name})`" :subtitle="item.raw.email"></v-list-item>
        </template>
      </v-input-browser>
      <!-- <v-btn v-if="!impersonated" :href="impersonateRoute" :disabled="!selected">Impersonate</v-btn> -->
    </div>
  </v-bottom-sheet>

</template>

<script>
export default {
  data () {
    return {

    }
  },
  created () {

  }
}
</script>

<style lang="scss" scoped>

</style>
