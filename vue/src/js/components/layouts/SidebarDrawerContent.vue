<template>
  <v-navigation-drawer
    ref="navigationDrawer"
    id="navigation-drawer"
    :model-value="status"
    @update:model-value="emit('update:status', $event)"
    :rail="rail"
    :expand-on-hover="isHoverable"
    :location="options.location"
    :rail-width="options.railWidth ?? 56"
    :persistent="effectivePersistent"
    :permanent="effectivePermanent"
    :temporary="effectiveTemporary"
    :width="width"
  >
    <template v-slot:prepend>
      <v-list class="ue-sidebar__info">
        <v-list-item
          :subtitle="store.getters.appEmail"
          :title="store.getters.appName"
          class="ue-sidebar__info-item"
        >
          <template v-slot:prepend>
            <v-avatar class="ue-sidebar__avatar" color="primary" rounded="lg">
              <ue-svg-icon class="ue-sidebar__logo" :symbol="miniSymbol" />
            </v-avatar>
          </template>
          <template v-slot:append>
            <v-btn
              v-if="lgAndUp"
              :icon="railManual ? 'mdi-chevron-right' : 'mdi-chevron-left'"
              :color="railManual ? 'primary' : 'grey'"
              variant="text"
              @click="emit('rail-toggle')"
              class="sidebar-toggle-btn"
              size="small"
              density="comfortable"
              title="Toggle Fixed Sidebar"
            />
          </template>
        </v-list-item>
      </v-list>
      <v-divider class="ue-sidebar__divider" />
    </template>

    <ue-navigation-group
      :items="items"
      :hideIcons="hideIcons"
      :showTooltip="rail && !isHoverable"
      id="ue-sidebar__menu"
    />

    <template v-slot:append>
      <template v-if="!store.getters.isGuest">
        <v-divider class="ue-sidebar__divider" />
        <v-list class="ue-sidebar__user">
          <v-list-item
            :title="store.getters.userProfile.name"
            :subtitle="store.getters.userProfile.email"
            class="ue-sidebar__info-item"
          >
            <template v-slot:prepend>
              <v-avatar
                :image="store.getters.userProfile.avatar_url || undefined"
                icon="mdi-account-outline"
                color="primary-lighten-4"
                @click="$openProfileDialog"
              />
            </template>
            <template v-slot:subtitle>
              <v-tooltip :text="store.getters.userProfile.email" location="top">
                <template v-slot:activator="tooltipActivator">
                  <div class="v-list-item-subtitle" v-bind="tooltipActivator.props">
                    {{ store.getters.userProfile.email }}
                  </div>
                </template>
              </v-tooltip>
            </template>
            <template v-slot:append>
              <v-btn
                @click="emit('update:profileMenuOpen', !profileMenuOpen)"
                :icon="profileMenuOpen ? 'mdi-chevron-up' : 'mdi-chevron-down'"
                size="small"
                variant="text"
              />
            </template>
          </v-list-item>

          <v-expand-transition>
            <div v-if="profileMenuOpen" class="ue-sidebar__profile">
              <ue-navigation-group
                :items="profileMenu"
                :profileMenu="true"
                @activateMenu="emit('activateMenu', $event)"
              />

              <LogoutModal :csrf="$csrf()">
                <template v-slot:activator="{ props: activatorProps }">
                  <v-tooltip text="Logout" location="top" :disabled="!(rail && !isHoverable)">
                    <template v-slot:activator="tooltipActivator">
                      <div v-bind="tooltipActivator.props">
                        <v-list-item
                          prepend-icon="mdi-logout"
                          v-bind="activatorProps"
                          :disabled="store.getters.isGuest"
                          :title="$t('authentication.logout')"
                        />
                      </div>
                    </template>
                  </v-tooltip>
                </template>
              </LogoutModal>

              <v-dialog
                ref="aboutDialog"
                max-width="500"
                v-if="store.getters.versions && !store.getters.isGuest && !store.getters.isClient"
              >
                <template v-slot:activator="{ props: activatorProps }">
                  <v-list-item
                    prepend-icon="mdi-information-outline"
                    v-bind="activatorProps"
                    :title="$t('About')"
                  />
                </template>
                <template v-slot:default="{ isActive }">
                  <v-card :title="$t('About')">
                    <v-card-text>
                      <div
                        v-for="(version, key) in store.getters.versions"
                        :key="key"
                        class="d-flex align-center my-1"
                      >
                        {{ $headline(key) }}:
                        <v-chip variant="outlined" color="primary" class="ml-2">{{ version }}</v-chip>
                      </div>
                      <div
                        v-if="store.getters.isSuperAdmin"
                        v-for="key in ['appName', 'appEnv', 'appDebug']"
                        :key="key"
                        class="d-flex align-center my-1"
                      >
                        {{ key === 'appDebug' ? 'Debug Mode' : $headline(key) }}:
                        <v-chip
                          variant="outlined"
                          :color="
                            key === 'appDebug'
                              ? store.getters[key]
                                ? 'success'
                                : 'error'
                              : 'primary'
                          "
                          class="ml-2"
                        >
                          {{
                            key === 'appDebug'
                              ? store.getters[key]
                                ? 'Active'
                                : 'Inactive'
                              : store.getters[key]
                          }}
                        </v-chip>
                      </div>
                    </v-card-text>
                    <v-divider></v-divider>
                    <v-card-actions>
                      <v-spacer></v-spacer>
                      <v-btn
                        variant="outlined"
                        :text="$t('Close')"
                        @click="isActive.value = false"
                      ></v-btn>
                    </v-card-actions>
                  </v-card>
                </template>
              </v-dialog>
            </div>
          </v-expand-transition>
        </v-list>
      </template>
      <slot name="bottom"></slot>
    </template>
  </v-navigation-drawer>
</template>

<script setup>
  import { useStore } from 'vuex'
  import { useDisplay } from 'vuetify'
  import LogoutModal from '__components/others/LogoutModal.vue'

  defineProps({
    items: { type: Array, required: true },
    profileMenu: { type: Array, default: () => [] },
    miniSymbol: { type: String, default: 'main-logo-dark' },
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
  })

  const emit = defineEmits(['update:status', 'update:profileMenuOpen', 'activateMenu', 'rail-toggle'])

  const store = useStore()
  const { lgAndUp } = useDisplay()
</script>
