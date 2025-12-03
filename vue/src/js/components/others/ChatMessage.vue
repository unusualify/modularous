<template>
  <div
    :class="[
      'd-flex mb-4 w-100',
      reverse ? 'flex-row-reverse' : 'flex-row'
    ]"
    >
    <div
      :style="[
        $vuetify.display.smAndUp ? 'max-width: 60%; min-width: 30%;' : 'max-width: 100%; min-width: 100%;'
      ]"
      :class="[
        'd-flex bg-grey-lighten-6 elevation-2 px-4 py-3 rounded position-relative',
        reverse ? 'flex-row-reverse' : 'flex-row',
        isUnread ? 'v-input-chat__message--unread' : ''
      ]"
      @mouseenter="startReading"
    >
      <!-- Avatar with time below -->
      <div class="d-flex flex-column align-center" :class="reverse ? 'ml-3' : 'mr-3'">
        <v-tooltip
          v-if="$vuetify.display.smAndDown"
          :text="formatDate(message)"
          location="top"
        >
          <template v-slot:activator="{ props }">
            <v-avatar
              :size="$vuetify.display.smAndUp ? avatarSize : mobileAvatarSize"
              :image="message.user_profile.avatar_url"
              v-bind="props"
            />
          </template>
        </v-tooltip>
        <v-avatar
          v-else
          :size="avatarSize"
          :image="message.user_profile.avatar_url"
        />
        <span
          v-if="$vuetify.display.smAndUp"
          class="text-caption text-grey-darken-1 mt-1"
          style="font-size: 10px; white-space: nowrap; text-align: center;"
        >
          {{ formatDate(message) }}
        </span>
      </div>

      <div
        :stylex="{ width: `calc(50% - ${avatarSize}px)` }"
        class="w-100">
        <!-- Header with name and icons (always clear) -->
        <div
          :class="[
            'text-grey text-caption w-100 d-flex justify-space-between',
            reverse ? 'flex-row-reverse' : 'flex-row'
          ]">

          <div :class="[
            $vuetify.display.smAndUp ? 'w-50' : 'w-75',
            reverse ? 'text-end' : 'text-start'
          ]"
          >
            <div>{{ message.user_profile.name }}</div>
          </div>

          <div class="d-flex justify-end">
            <v-icon
              v-if="!noStarring"
              :icon="message.is_starred ? 'mdi-star' : 'mdi-star-outline'"
              :color="message.is_starred ? 'secondary' : 'grey'"
              @click="updateMessage('is_starred', !message.is_starred)"
            />
            <v-icon
              v-if="!noPinning"
              :icon="message.is_pinned ? 'mdi-pin' : 'mdi-pin-outline'"
              :color="message.is_pinned ? 'primary' : 'grey'"
              @click="updateMessage('is_pinned', !message.is_pinned)"
            />
          </div>
        </div>

        <!-- Blurred content wrapper -->
        <div
          :class="[
            'message-content',
            isUnread ? 'message-content--unread' : ''
          ]"
        >
          <!-- Message content -->
          <div :class="['d-flex mt-2 text-break position-relative', reverse ? 'flex-row-reverse' : 'flex-row']">
            <div class="w-100" style="color: #32454A; font-weight: 400; font-size: 12px;">
              <template v-if="message.content && message.content.length > contentTruncateLength">
                <div v-if="isExpanded" v-html="formattedContent"></div>
                <div v-else>
                  <span v-html="formattedTruncatedContent"></span>
                  <span class="text-grey-darken-1">...</span>
                </div>

                <v-expand-transition>
                  <div v-if="message.content && message.content.length > contentTruncateLength" class="mt-1">
                    <v-btn
                      :text="isExpanded ? $t('Show less') : $t('Show more')"
                      variant="plain"
                      size="small"
                      color="primary"
                      @click="toggleExpand"
                    />
                  </div>
                </v-expand-transition>
              </template>
              <template v-else>
                <div v-html="formattedContent"></div>
              </template>
            </div>
          </div>

          <!-- Attachments -->
          <div v-if="message.attachments.length > 0" class="mt-2 py-1 rounded">
            <ue-title :text="$t('Attachments')" padding="b-2" type="caption" color="none" transform="none"/>
            <ue-filepond-preview
              :source="message.attachments"
              image-size="24"
              show-file-name
              no-overlay
              show-datex
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    props: {
      modelValue: {
        type: Object,
        required: true
      },
      avatarSize: {
        type: Number,
        default: 50
      },
      mobileAvatarSize: {
        type: Number,
        default: 25
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
      contentTruncateLength: {
        type: Number,
        default: 50
      }
    },
    data() {
      return {
        readingTimer: null,
        isExpanded: false
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
      truncatedContent() {
        if (this.message.content && this.message.content.length > this.contentTruncateLength) {
          return this.message.content.substring(0, this.contentTruncateLength);
        }
        return this.message?.content ?? '';
      },
      // FormattedContent and formattedTruncatedContent are used to format the content of the message with the new lines.
      formattedContent() {
        return this.message.content ? this.message.content.replace(/\n/g, '<br>') : '';
      },
      formattedTruncatedContent() {
        if (this.message.content.length > this.contentTruncateLength) {
          return this.message.content.substring(0, this.contentTruncateLength).replace(/\n/g, '<br>');
        }
        return this.message?.content?.replace(/\n/g, '<br>') ?? '';
      }
    },
    methods: {
      formatDate(message) {
        if (!message.created_at) {
          return '';
        }

        const date = new Date(message.created_at);
        const elapsedMilliSeconds = this.getElapsedMilliseconds(date);
        const elapsedDays = this.getElapsedDays(elapsedMilliSeconds);

        // Today
        if (elapsedDays === 0) {
          const elapsedHours = this.getElapsedHours(elapsedMilliSeconds);
          if (elapsedHours === 0) {
            const elapsedMinutes = this.getElapsedMinutes(elapsedMilliSeconds);
            if (elapsedMinutes === 0) {
              return 'just now';
            }
            return this.formatMinutes(elapsedMinutes);
          }
          return this.formatHours(elapsedHours);
        }

        // Yesterday
        if (elapsedDays === 1) {
          return 'yesterday';
        }

        // This week
        if (elapsedDays < 7) {
          return `${elapsedDays} days ago`;
        }

        // This month
        if (elapsedDays < 30) {
          const weeks = this.getElapsedWeeks(elapsedDays);
          return this.formatWeeks(weeks);
        }

        // This year
        if (elapsedDays < 365) {
          const months = this.getElapsedMonths(elapsedDays);
          return this.formatMonths(months);
        }

        // Older than a year
        const years = this.getElapsedYears(elapsedDays);
        return this.formatYears(years);
      },
      updateMessage(field, value) {
        let endpoint = this.updateEndpoint.replace(':id', this.input.id);

        let self = this;
        axios.put(endpoint, {
          [field]: value
        }).then(response => {
          self.input = {
            ...self.input,
            [field]: value
          };
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
      getElapsedMilliseconds(date) {
        const now = new Date();
        return now.getTime() - date.getTime();
      },
      getElapsedMinutes(milliSeconds) {
        return Math.floor(milliSeconds / (1000 * 60));
      },
      getElapsedHours(milliSeconds) {
        return Math.floor(milliSeconds / (1000 * 60 * 60));
      },
      getElapsedDays(milliSeconds) {
        return Math.floor(milliSeconds / (1000 * 60 * 60 * 24));
      },
      getElapsedWeeks(days) {
        return Math.floor(days / 7);
      },
      getElapsedMonths(days) {
        return Math.floor(days / 30);
      },
      getElapsedYears(days) {
        return Math.floor(days / 365);
      },
      formatMinutes(minutes) {
        return minutes === 1 ? '1 minute ago' : `${minutes} minutes ago`;
      },
      formatHours(hours) {
        return hours === 1 ? '1 hour ago' : `${hours} hours ago`;
      },
      formatWeeks(weeks) {
        return weeks === 1 ? '1 week ago' : `${weeks} weeks ago`;
      },
      formatMonths(months) {
        return months === 1 ? '1 month ago' : `${months} months ago`;
      },
      formatYears(years) {
        return years === 1 ? '1 year ago' : `${years} years ago`;
      },
    },
    beforeUnmount() {
      if (this.readingTimer) {
        clearTimeout(this.readingTimer);
      }
    }
  }
</script>

<style lang="scss">
.message-content--unread {
  filter: blur(2px);
  transition: all 1s cubic-bezier(0.4, 0, 0.2, 1);
}

.v-input-chat__message--unread {
  opacity: 0.9;
  transition: all 1s cubic-bezier(0.4, 0, 0.2, 1);

  &:hover {
    opacity: 1;
    border-width: 1px;
    border-color: rgba(var(--v-theme-primary), 0.6);
    transform: scale(1.002);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);

    .message-content--unread {
      filter: blur(0);
    }
  }
}
</style>
