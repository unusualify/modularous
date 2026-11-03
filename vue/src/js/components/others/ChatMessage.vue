<template>
  <div
    :class="[
      'v-input-chat__row d-flex align-start mb-4 w-100',
      reverse ? 'flex-row-reverse' : 'flex-row'
    ]"
    >
    <!-- Avatar with the sender name below it -->
    <div
      class="v-input-chat__author d-flex flex-column align-center flex-shrink-0"
      :class="reverse ? 'ml-3' : 'mr-3'"
      :style="{ width: `${authorWidth}px` }"
    >
      <v-avatar
        :size="$vuetify.display.smAndUp ? avatarSize : mobileAvatarSize"
        :image="message.user_profile.avatar_url"
        rounded="lg"
        class="v-input-chat__avatar"
      />
      <span class="v-input-chat__author-name text-truncate mt-1">{{ message.user_profile.name }}</span>
    </div>

    <div
      :class="[
        'v-input-chat__bubble position-relative',
        reverse ? 'v-input-chat__bubble--own' : '',
        isUnread ? 'v-input-chat__message--unread' : '',
        editing ? 'v-input-chat__message--editing' : ''
      ]"
      @mouseenter="startReading"
    >
      <div
        :class="[
          'v-input-chat__message-actions d-flex justify-end',
          message.is_starred || message.is_pinned ? 'v-input-chat__message-actions--active' : ''
        ]"
      >
        <v-tooltip v-if="canEdit && !editing" :text="$t('Edit')" location="top">
          <template v-slot:activator="{ props: activatorProps }">
            <v-icon
              v-bind="activatorProps"
              icon="mdi-pencil-outline"
              color="grey"
              size="small"
              @click="$emit('edit', message)"
            />
          </template>
        </v-tooltip>
        <v-tooltip v-if="canDelete && !editing" :text="$t('Delete')" location="top">
          <template v-slot:activator="{ props: activatorProps }">
            <v-icon
              v-bind="activatorProps"
              icon="mdi-delete-outline"
              color="grey"
              size="small"
              @click="$emit('delete', message)"
            />
          </template>
        </v-tooltip>
        <template v-if="!noStarring">
          <span v-if="updatingFields.is_starred" class="v-input-chat__message-action-loader">
            <v-progress-circular indeterminate size="14" width="2" color="secondary" />
          </span>
          <v-tooltip v-else :text="message.is_starred ? $t('Unstar') : $t('Star')" location="top">
            <template v-slot:activator="{ props: activatorProps }">
              <v-icon
                v-bind="activatorProps"
                :icon="message.is_starred ? 'mdi-star' : 'mdi-star-outline'"
                :color="message.is_starred ? 'secondary' : 'grey'"
                size="small"
                @click="updateMessage('is_starred', !message.is_starred)"
              />
            </template>
          </v-tooltip>
        </template>
        <template v-if="!noPinning">
          <span v-if="updatingFields.is_pinned" class="v-input-chat__message-action-loader">
            <v-progress-circular indeterminate size="14" width="2" color="primary" />
          </span>
          <v-tooltip v-else :text="message.is_pinned ? $t('Unpin') : $t('Pin')" location="top">
            <template v-slot:activator="{ props: activatorProps }">
              <v-icon
                v-bind="activatorProps"
                :icon="message.is_pinned ? 'mdi-pin' : 'mdi-pin-outline'"
                :color="message.is_pinned ? 'primary' : 'grey'"
                size="small"
                @click="updateMessage('is_pinned', !message.is_pinned)"
              />
            </template>
          </v-tooltip>
        </template>
      </div>

      <!-- Blurred content wrapper -->
      <div
        :class="[
          'message-content',
          isUnread ? 'message-content--unread' : ''
        ]"
      >
        <div class="v-input-chat__content text-break">
          <template v-if="message.content && message.content.length > contentTruncateLength">
            <ue-well-print
              v-if="isExpanded"
              :text="message.content"
              :no-linkify="$attrs.noLinkify"
              class="w-100"
            />
            <div v-else>
              <ue-well-print
                :text="truncatedContent"
                :full-text="message.content"
                :no-linkify="$attrs.noLinkify"
                class="w-100"
              />
              <span class="text-grey-darken-1">...</span>
            </div>

            <v-btn
              :text="isExpanded ? $t('Show less') : $t('Show more')"
              class="v-input-chat__expand px-0"
              variant="plain"
              size="small"
              color="primary"
              @click="toggleExpand"
            />
          </template>
          <ue-well-print
            v-else
            :text="message.content"
            :no-linkify="$attrs.noLinkify"
            class="w-100"
          />
        </div>

        <!-- Attachments -->
        <div v-if="message.attachments.length > 0" class="mt-2 py-1">
          <ue-title :text="$t('Attachments')" padding="b-2" type="caption" color="none" transform="none"/>
          <ue-filepond-preview
            :source="message.attachments"
            image-size="24"
            show-file-name
            no-overlay
          />
        </div>
      </div>

      <div class="v-input-chat__meta d-flex align-center ga-2 mt-2">
        <span>{{ formatDate(message) }}</span>
        <span v-if="isEdited" class="v-input-chat__message-edited">{{ $t('Edited') }}</span>
      </div>
    </div>
  </div>
</template>

<script>
  import WellPrint from '@/components/shared/WellPrint.vue'
  import { isObject } from '@/utils/helpers'

  export default {
    components: {
      WellPrint
    },
    emits: ['update:modelValue', 'edit', 'delete'],
    props: {
      modelValue: {
        type: Object,
        required: true
      },
      avatarSize: {
        type: Number,
        default: 40
      },
      mobileAvatarSize: {
        type: Number,
        default: 32
      },
      reverse: {
        type: Boolean,
        default: false
      },
      updateEndpoint: {
        type: String,
        required: true
      },
      noStarring: {
        type: Boolean,
        default: false
      },
      noPinning: {
        type: Boolean,
        default: false
      },
      // called before pinning, resolves to false to cancel the pin (e.g. the pin limit is reached),
      // or to an object of extra request data, e.g. { pin_duration: 24 }
      beforePin: {
        type: Function,
        default: null
      },
      contentTruncateLength: {
        type: Number,
        default: 50
      },
      // whether this message is currently being edited in the composer
      editing: {
        type: Boolean,
        default: false
      }
    },
    data() {
      return {
        readingTimer: null,
        isExpanded: false,
        now: Date.now(),
        editWindowTimer: null,
        // fields with a pending update request, e.g. { is_pinned: true }
        updatingFields: {}
      }
    },
    computed: {
      input: {
        get() {
          return this.modelValue;
        },
        set(value) {
          this.message = value;
          this.$emit('update:modelValue', value);
        }
      },
      message() {
        return this.modelValue;
      },
      isUnread() {
        return !this.message.is_read && !this.reverse;
      },
      isEdited() {
        return !!this.message.edited_at;
      },
      canEdit() {
        // only the sender sees the edit action; backend enforces the same rules
        if (!this.reverse || this.message.loading || !this.message.id) return false;
        if (this.isEdited || !this.message.is_editable || !this.message.editable_until) return false;

        return new Date(this.message.editable_until).getTime() > this.now;
      },
      canDelete() {
        // deleting follows the editing rules; backend enforces the same rules
        if (!this.reverse || this.message.loading || !this.message.id) return false;
        if (this.isEdited || !this.message.is_deletable || !this.message.editable_until) return false;

        return new Date(this.message.editable_until).getTime() > this.now;
      },
      // the name below the avatar may be wider than the avatar itself
      authorWidth() {
        return (this.$vuetify.display.smAndUp ? this.avatarSize : this.mobileAvatarSize) + 16;
      },
      truncatedContent() {
        if (this.message.content && this.message.content.length > this.contentTruncateLength) {
          return this.message.content.substring(0, this.contentTruncateLength);
        }
        return this.message?.content ?? '';
      }
    },
    methods: {
      formatDate(message) {
        let formattedDate = window.$moment().fromNow();

        if (message.created_at) {
          let date = new Date(message.created_at);

          if (Date.now() - date.getTime() < 48 * 60 * 60 * 1000) {
            formattedDate = window.$moment(date).locale(this.$i18n.locale).fromNow();

          } else if (Date.now() - date.getTime() < 7 * 24 * 60 * 60 * 1000) {
            formattedDate = window.$moment(date).locale(this.$i18n.locale).format('dddd');

          } else {
            formattedDate = window.$moment(message.created_at).locale(this.$i18n.locale).format('MMM Do YY');
          }
        }

        return formattedDate;
      },

      async updateMessage(field, value) {
        if (this.updatingFields[field]) return;

        let extra = {};

        if (field === 'is_pinned' && value && this.beforePin) {
          const result = await this.beforePin(this.input);

          if (!result) return;

          if (isObject(result)) extra = result;
        }

        let endpoint = this.updateEndpoint.replace(':id', this.input.id);

        let self = this;
        this.updatingFields[field] = true;

        axios.put(endpoint, {
          [field]: value,
          ...extra
        }).then(response => {
          self.input = {
            ...self.input,
            [field]: value,
            // the server sets the pin times
            ...(field === 'is_pinned' && isObject(response.data)
              ? { pinned_at: response.data.pinned_at, pinned_until: response.data.pinned_until }
              : {})
          };
        }).finally(() => {
          self.updatingFields[field] = false;
        });
      },
      startReading() {
        if (!this.isUnread) return;

        // Wait for transition to complete before marking as read
        this.readingTimer = setTimeout(() => {
          this.markAsRead();
        }, 1000); // Matches the transition duration
      },
      markAsRead() {
        // message.is_read = true;
        this.updateMessage('is_read', true);
      },
      toggleExpand() {
        this.isExpanded = !this.isExpanded;
      },
      scheduleEditWindowExpiry() {
        if (this.editWindowTimer) {
          clearTimeout(this.editWindowTimer);
          this.editWindowTimer = null;
        }

        if (!this.reverse || !this.message.editable_until) return;

        const remaining = new Date(this.message.editable_until).getTime() - Date.now();

        if (remaining > 0) {
          // re-evaluate canEdit once the edit window closes
          this.editWindowTimer = setTimeout(() => {
            this.now = Date.now();
            this.editWindowTimer = null;
          }, remaining + 100);
        }
      },
    },
    watch: {
      'message.editable_until': {
        handler() {
          this.now = Date.now();
          this.scheduleEditWindowExpiry();
        },
        immediate: true
      }
    },
    beforeUnmount() {
      if (this.readingTimer) {
        clearTimeout(this.readingTimer);
      }
      if (this.editWindowTimer) {
        clearTimeout(this.editWindowTimer);
      }
    }
  }
</script>

<style lang="scss">
.v-input-chat__author-name {
  max-width: 100%;
  font-size: 12px;
  line-height: 16px;
  color: rgb(var(--v-theme-grey-darken-1));
}

.v-input-chat__avatar {
  background: rgb(var(--v-theme-grey-lighten-3));
}

.v-input-chat__bubble {
  max-width: min(70%, 520px);
  min-width: 160px;
  padding: 10px 14px 10px;
  background: rgb(var(--v-theme-surface));
  border: 1px solid rgba(var(--v-border-color), 0.16);
  border-radius: 12px;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;

  @media (max-width: 599px) {
    max-width: calc(100% - 56px);
  }
}

.v-input-chat__content {
  font-size: 13px;
  line-height: 18px;
  color: rgb(var(--v-theme-primary-darken-3));
}

.v-input-chat__expand {
  min-width: 0;
  height: auto !important;
}

.v-input-chat__meta {
  font-size: 11px;
  line-height: 14px;
  color: rgb(var(--v-theme-grey-darken-1));
}

// starring / pinning stays out of the way until the message is hovered
.v-input-chat__message-actions {
  position: absolute;
  top: 6px;
  right: 8px;
  gap: 2px;
  padding: 2px 4px;
  border-radius: 8px;
  background: rgb(var(--v-theme-surface));
  opacity: 0;
  transition: opacity 0.15s ease;
  z-index: 2;
}

.v-input-chat__bubble:hover .v-input-chat__message-actions,
.v-input-chat__message-actions--active,
.v-input-chat__message-actions:focus-within {
  opacity: 1;
}

@media (hover: none) {
  .v-input-chat__message-actions {
    opacity: 1;
  }
}

.v-input-chat__message-action-loader {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 20px;
  height: 20px;
}

.v-input-chat__message--editing {
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.18) !important;
  transition: box-shadow 0.2s ease;
}

.v-input-chat__message-edited {
  font-style: italic;
}

.message-content--unread {
  filter: blur(2px);
  transition: all 1s cubic-bezier(0.4, 0, 0.2, 1);
}

.v-input-chat__message--unread {
  opacity: 0.9;
  transition: all 1s cubic-bezier(0.4, 0, 0.2, 1);

  &:hover {
    opacity: 1;
    border-color: rgba(var(--v-theme-primary), 0.6);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);

    .message-content--unread {
      filter: blur(0);
    }
  }
}
</style>
