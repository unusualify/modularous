<script setup>
import { ref, computed } from 'vue';
import { useInput, makeInputProps, makeInputEmits, makeFilepondProps } from '@/hooks';

// This component renders a fragment (two sibling root elements: the avatar
// wrapper div and the v-input-filepond), so Vue can't auto-inherit fall-
// through attrs onto a single root. Combined with form-schema passing many
// Vuetify input attrs we don't declare (`placeholder`, `errorMessages`,
// `prependIcon`, etc.), this triggers the "Extraneous non-props attributes"
// warning. We disable auto-inherit and forward `$attrs` explicitly to the
// underlying filepond input below — that is the element those attrs are
// actually meant for.
defineOptions({ inheritAttrs: false });

const props = defineProps({
  disabled: {
    type: Boolean,
    default: false,
  },
  ...makeInputProps(),
  ...makeFilepondProps(),
});

const emit = defineEmits(makeInputEmits);

const Input = useInput(props, { emit, updateModelValue: (val, old) => {
  // An empty list coming from FilePond only means its replace step (old file
  // removed before the new one uploads). Emitting it would let submitOnChange
  // save an empty avatar and treat the new upload as clean. Explicit removal
  // goes through `removeAvatar` instead.
  if (!Array.isArray(val) || val.length === 0) {
    return;
  }
  if (val.length > 1) {
    Input.input.value = [val[1]];
  } else {
    emit('update:modelValue', val);
  }
} });

const fileLoading = ref(false);

const FilepondRef = ref(null);

const browse = () => {
  if (fileLoading.value || props.disabled) {
    return;
  }
  FilepondRef.value.browse();
}

const hasAvatar = computed(() => Array.isArray(Input.input.value) && Input.input.value.length > 0);

const canRemove = computed(() => hasAvatar.value && !props.disabled && !fileLoading.value);

const confirmRemoveOpen = ref(false);

/**
 * Removal saves immediately (submitOnChange), so ask before doing it.
 */
const askRemoveAvatar = () => {
  if (!canRemove.value) {
    return;
  }
  confirmRemoveOpen.value = true;
}

/**
 * Clear the avatar: emit an empty list (the repository deletes existing
 * fileponds on an empty payload) and drop FilePond's own file items so the
 * inner pond stays in sync. The pond's resulting empty update is swallowed
 * by `updateModelValue` above, so only this single emit reaches the form.
 */
const removeAvatar = () => {
  if (!canRemove.value) {
    return;
  }
  emit('update:modelValue', []);
  FilepondRef.value?.removeFiles?.();
}

const activateLoading = () => {
  console.log('activateLoading');
  fileLoading.value = true;
}

const deactivateLoading = () => {
  console.log('deactivateLoading');
  fileLoading.value = false;
}

</script>

<template>
  <div class="v-input-filepond__avatar-wrapper" @click="browse" >
    <v-btn
      v-if="canRemove"
      class="v-input-filepond__remove-icon"
      variant="flat"
      :aria-label="$t('Remove profile picture')"
      @click.stop="askRemoveAvatar"
    >
      <v-icon size="14">mdi-trash-can-outline</v-icon>
    </v-btn>
    <v-avatar
      size="72"
      color="grey-lighten-6"
      border="sm"
      :class="disabled ? '' : 'cursor-pointer'"
    >
      <v-img
        v-if="hasAvatar"
        alt="Avatar"
        :src="Input.input.value[0].source"
        cover
      >
      </v-img>
      <v-icon
        v-else
        icon="mdi-account"
        size="40"
        color="grey"
      ></v-icon>
    </v-avatar>
    <div v-if="!disabled || fileLoading" :class="[
      'v-input-filepond__edit-icon d-flex align-center justify-center',
      !fileLoading ? 'bg-primary' : 'bg-surface'
    ]">
      <v-progress-circular
        v-if="fileLoading"
        :size="16"
        :width="2"
        color="success"
        indeterminate
      ></v-progress-circular>
      <v-icon v-else size="14">mdi-camera-outline</v-icon>
    </div>
  </div>
  <v-input-filepond
    v-bind="$attrs"
    ref="FilepondRef"
    v-model="Input.input.value"
    class="v-input-filepond-avatar"

    :hide-details="hideDetails"
    :hint="hint"
    :min="min"
    :max-files="maxFiles"
    :rules="rules"
    :hint-weight="hintWeight"
    :end-points="endPoints"
    :label-weight="labelWeight"
    :subtitle="subtitle"
    :subtitle-weight="subtitleWeight"
    :accepted-file-types="acceptedFileTypes"

    :allow-multiple="false"
    :allow-replace="true"

    @loadingFile="activateLoading"
    @loadedFile="deactivateLoading"
  >
    <template v-slot:activator="activatorProps">

    </template>
  </v-input-filepond>
  <ue-modal
    v-model="confirmRemoveOpen"
    width-type="sm"
    :title="$t('Remove profile picture?')"
    :description="$t('Your profile picture will be removed.')"
    :confirm-text="$t('Remove')"
    :cancel-text="$t('Cancel')"
    :confirm-button-attributes="{ color: 'error', variant: 'elevated' }"
    :reject-button-attributes="{ color: 'grey-darken-1', variant: 'outlined' }"
    @confirm="removeAvatar"
  />
</template>

<style scoped lang="scss">
  .v-input-filepond-avatar {
    height: 0;
    max-height: 0;
    margin: 0;
    padding: 0;
    overflow: hidden;
    opacity: 0;
    pointer-events: none;
  }

  .v-input-filepond-avatar .filepond--root {
    background: transparent;
    border:    none;
    box-shadow:none;
    padding:   0;
  }

  .v-input-filepond__avatar-wrapper  {
    position: relative;
    display: inline-block;

    .v-input-filepond__edit-icon {
      position: absolute;
      bottom: 0;
      right: 0;
      width: 26px;
      height: 26px;
      border-radius: 50%;
      border: 2px solid rgb(var(--v-theme-surface));
      cursor: pointer;
    }

    // Always visible while an avatar exists (rendered with v-if="canRemove").
    .v-input-filepond__remove-icon {
      position: absolute;
      top: 0;
      right: 0;
      z-index: 1;
      // Light teal tint (opaque, so the photo underneath doesn't show through)
      // with a teal glyph: softer than the solid camera badge.
      background-color: rgb(230, 245, 245);
      color: rgb(5, 151, 155);
      border: 2px solid rgb(var(--v-theme-surface));

      // Match the camera badge (.v-input-filepond__edit-icon) exactly.
      width: 26px;
      height: 26px;
      min-width: 26px;
      padding: 0;
      border-radius: 50%;
    }
  }

</style>
