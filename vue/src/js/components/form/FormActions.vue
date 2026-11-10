<template>
  <div :class="[
      hasActions ? `d-flex flex-wrap align-center ga-2 py-3 justify-${justify}` : ''
    ]"
  >
    <slot name="prepend" v-bind="{item: modelValue, isEditing}"></slot>
    <template v-for="(action, key) in allActions">
      <v-tooltip
        :disabled="!action.icon || action.forceLabel"
        :location="action.tooltipLocation ?? 'top'"
      >
        <template v-slot:activator="tooltipActivatorScope">
          <v-switch v-if="action.type === 'publish'"
            :modelValue="editedItem[action.key ?? 'published'] ?? action.default ?? false"
            v-bind="{...action.componentProps, ...tooltipActivatorScope.props}"

            :disabled="action.disabled ?? false"
            @update:modelValue="handleAction(action)"
          />
          <ue-modal v-else-if="action.type === 'modal' && (action?.formAttributes?.hasSubmit ?? true ? action.endpoint : true) && action.schema"
            :close-on-content-click="false"
            transition="scale-transition"
            widthType="md"
            no-default-body-padding
            has-close-button
            has-fullscreen-button
            has-title-divider
            no-actions
            v-bind="action.modalAttributes ?? {}"
            :use-model-value="false"
            description-body-class="d-flex flex-column fill-height w-100"
          >
            <template v-slot:activator="modalActivatorScope">
              <v-badge v-if="isBadge(action)"
                v-bind="badgeProps(action)"
              >
                <v-btn
                  v-bind="{
                    ...formActionButtonProps(action),
                    ...modalActivatorScope.props,
                    ...tooltipActivatorScope.props
                  }"
                />
              </v-badge>
              <v-btn v-else
                v-bind="{
                  ...formActionButtonProps(action),
                  ...modalActivatorScope.props,
                  ...tooltipActivatorScope.props
                }"
              />
            </template>

            <template v-slot:body.description="formModalBodyScope">
              <ue-form
                :ref="`extra-form-${key}`"
                :modelValue="createModel(action.schema, action)"
                :title="action.formTitle ?? null"
                :schema="action.schema"
                :action-url="action?.formAttributes?.hasSubmit ?? true ? action.endpoint.replace(':id', modelValue.id) : null"
                :valid="valids[key]"
                :is-editing="action.isEditing ?? isEditing"

                class="w-100"
                :style="formModalBodyScope.isFullActive ? 'height: 90vh !important;' : 'height: 70vh !important;'"

                :border="false"
                fill-height
                scrollable

                has-divider
                has-submit
                button-text="Save"

                @submitted="$emit('actionComplete', { action })"
                @update:valid="valids[key] = $event"

                @updatex:modelValue="$log($event)"

                v-bind="action.formAttributes ?? {}"
              />
            </template>

          </ue-modal>
          <template v-else-if="action.type !== 'modal'">
            <v-badge v-if="isBadge(action)"
              v-bind="badgeProps(action)"
            >
              <v-btn
                v-bind="{
                  ...formActionButtonProps(action),
                  ...tooltipActivatorScope.props,
                }"
                @click="handleAction(action)"
              />
            </v-badge>
            <v-btn v-else
              v-bind="{...formActionButtonProps(action), ...tooltipActivatorScope.props}"
              @click="handleAction(action)"
            />
          </template>
        </template>
        <span>{{ action.tooltip ?? action.label }}</span>
      </v-tooltip>
    </template>
    <slot name="append" v-bind="{item: modelValue, isEditing}"></slot>
  </div>
</template>

<script>
import { computed } from 'vue'
import { useItemActions } from '@/hooks'
import { getModel } from '@/utils/getFormData'
import useGenerate from '@/hooks/utils/useGenerate'
import useBadge from '@/hooks/utils/useBadge'

export default {
  name: 'FormActions',
  emits: ['actionComplete'],
  props: {
    modelValue: {
      type: Object,
      required: true
    },
    isEditing: {
      type: Boolean,
      required: false
    },
    actions: {
      type: Object,
      required: true
    },
    formDirty: {
      type: Boolean,
      default: false
    },
    justify: {
      type: String,
      default: 'end'
    },
  },
  setup(props, context) {
    const { handleAction, allActions, hasActions } = useItemActions(props, {
      ...context,
      actionItem: props.modelValue
    })
    const { generateButtonProps } = useGenerate()
    const { isBadge, badgeProps } = useBadge()
    const valids = computed(() => allActions.value.map(action => true))

    const createModel = (schema, action) => {
      // the parent item is used for checking conditions (e.g. is_chat_closed);
      // the schema fields still win, and only schema fields are submitted
      const model = { ...props.modelValue, ...getModel(schema, props.modelValue) }
      if (action.isEditing === false) {
        delete model.id
      }
      return model
    }

    // Disabled buttons get no color so they render as neutral grey instead of a faded theme color
    const formActionButtonProps = (action) => {
      const buttonProps = generateButtonProps(action)
      const hasLabel = !!action.forceLabel

      return {
        ...buttonProps,
        class: [
          'ue-form-action',
          hasLabel ? 'ue-form-action--label' : 'ue-form-action--icon',
          buttonProps.class,
        ],
        prependIcon: hasLabel ? (buttonProps.prependIcon ?? action.icon) : buttonProps.prependIcon,
        variant: hasLabel
          ? (!action.variant || action.variant === 'elevated' ? 'flat' : action.variant)
          : (action.variant ?? 'outlined'),
        color: buttonProps.disabled ? undefined : (action.color ?? 'primary'),
        rounded: 'pill',
        size: 'default',
        // Icon buttons size from their own density offsets; compact matches the 44px label height
        density: hasLabel ? 'default' : 'compact',
      }
    }

    return {
      hasActions,
      allActions,
      handleAction,

      valids,

      createModel,
      formActionButtonProps,
      isBadge,
      badgeProps
    }
  },
}
</script>

<!-- Unscoped so buttons passed through the prepend/append slots can share the .ue-form-action look -->
<style>
.ue-form-action.v-btn {
  letter-spacing: 0;
  text-transform: none;
  font-weight: 500;
}

.ue-form-action--icon.v-btn--variant-outlined:not(.v-btn--disabled) {
  background-color: color-mix(in srgb, currentColor 12%, transparent);
}

.ue-form-action.v-btn--disabled {
  opacity: 1;
  box-shadow: none;
  border-color: transparent;
  background-color: rgba(var(--v-theme-on-surface), 0.08);
  color: rgba(var(--v-theme-on-surface), 0.45);
}

.ue-form-action.v-btn--disabled .v-btn__overlay {
  opacity: 0;
}
</style>

