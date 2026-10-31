<template>
  <div>
    <ue-modal v-if="formSchema && processableModel"
      ref="formModal"
      v-model="formActive"
      scrollablex
      transition="dialog-bottom-transition"
      width-type="md"
      full
      eager
    >
      <template v-slot:body="formModalBodyScope">
        <v-card class="fill-height d-flex flex-column">
          <ue-form
            ref="UeForm"
            :border="false"

            v-model="processableModel"
            :schema="formSchema"

            :title="title"
            :style="formModalBodyScope.isFullActive ? 'height: 90vh !important;' : 'height: 70vh !important;'"

            is-editing
            fill-height
            scrollable
            has-divider
          />

          <v-divider class="mx-6 mt-4"/>

          <v-card-actions class="px-6 flex-grow-0">
            <v-spacer></v-spacer>
            <v-btn-secondary
              :slim="false"
              variant="outlined"
              @click="$refs.formModal.close()"
            >
              {{ $t('Close') }}
            </v-btn-secondary>
            <v-btn-secondary
              v-if="$store.getters.isSuperAdmin"
              :slim="false"
              variant="outlined"
              @click="UeForm.validate()"
            >
              {{ $t('Validate') }}
            </v-btn-secondary>
            <v-btn-primary
              :slim="false"
              variant="elevated"
              @click="updateProcessable"
              :disabled="!formIsValid"
              >
              {{ $t('fields.save') }}
            </v-btn-primary>
          </v-card-actions>

        </v-card>
      </template>
    </ue-modal>

    <v-card
      variant="flat"
      :class="['ue-process-card w-100 h-100 d-flex flex-column', `ue-process-card--${cardTone}`]"
    >
      <v-skeleton-loader v-if="loading" type="table-heading, list-item-three-line" class="w-100 h-100" style="min-height: 200px;"></v-skeleton-loader>

      <template v-else-if="processModel">
        <div class="ue-process-card__body d-flex flex-column flex-grow-1">

          <!-- Processable Title -->
          <div class="d-flex align-start justify-space-between ga-2 mb-3">
            <div class="ue-process-card__title text-wrap">
              {{ title }}
            </div>
            <v-chip
              :prepend-icon="status.icon"
              :color="cardTone === 'neutral' ? undefined : cardTone"
              variant="tonal"
              size="small"
              class="flex-shrink-0"
            >
              {{ status.label }}
            </v-chip>
          </div>

          <!-- Details -->
          <div class="ue-process-card__details">
            <div v-for="detail in flattenedProcessableDetails" :key="detail.title" class="ue-process-card__row">
              <div class="ue-process-card__label">{{ detail.title }}</div>
              <div class="ue-process-card__value">{{ detail.value }}</div>
            </div>

            <template v-if="showProcessableDetails && processableModel">
              <div v-for="formInput in formSchema" :key="formInput.name" class="ue-process-card__row">
                <div class="ue-process-card__label">{{ formInput.label ?? $t('Unknown') }}</div>
                <div class="ue-process-card__value">
                  <ue-filepond-preview v-if="formInput.type === 'input-filepond'"
                    :source="processableModel?.[formInput.name]"
                    show-inline-file-name
                    image-size="20"
                    max-file-name-length="18"
                  />
                  <template v-else-if="(formInput.type === 'text' && formInput.ext === 'date') || (['input-date', 'date-input'].includes(formInput.type))">
                    {{ processableModel?.[formInput.name] ? $d(processableModel[formInput.name], formInput.displayDateFormat ?? 'numeric') : '' }}
                  </template>
                  <template v-else>
                    {{ processableModel?.[formInput.name] }}
                  </template>
                </div>
              </div>
            </template>

            <!-- History -->
            <div v-if="processModel.last_history && processModel.last_history.reason" class="ue-process-card__row">
              <div class="ue-process-card__label">{{ processModel.status_reason_label }}</div>
              <div class="ue-process-card__value d-flex align-center" style="min-width: 0;">
                <div
                  ref="reasonTextEl"
                  class="flex-grow-1 text-truncate"
                  style="min-width: 0;"
                >
                  {{ processModel.last_history.reason }}
                </div>
                <v-menu
                  v-if="hasReasonOverflow"
                  v-model="reasonMenuOpen"
                  :close-on-content-click="false"
                  location="bottom end"
                  offset="4"
                >
                  <template v-slot:activator="{ props: activatorProps }">
                    <v-btn
                      :icon="reasonMenuOpen ? 'mdi-chevron-up' : 'mdi-chevron-down'"
                      size="x-small"
                      variant="text"
                      density="compact"
                      class="ms-1 flex-shrink-0"
                      v-bind="activatorProps"
                    />
                  </template>
                  <v-card max-width="360">
                    <v-card-text
                      class="text-body-medium text-break"
                      style="white-space: pre-line;"
                    >
                      {{ processModel.last_history.reason }}
                    </v-card-text>
                  </v-card>
                </v-menu>
              </div>
            </div>
          </div>

          <p v-if="!(showProcessableDetails && processableModel)" class="text-body-small text-medium-emphasis mt-2 mb-0">
            {{ informationalMessage }}
          </p>

          <v-spacer></v-spacer>

          <!-- Actions -->
          <div class="d-flex flex-wrap justify-end ga-2 pt-4">
            <v-btn v-if="hasProcessableEditing && formSchema && processableModel"
              ref="editButton"
              v-bind="actionButtonProps"
              :color="bothFormAndProcessableValid ? undefined : 'secondary'"
              :prepend-icon="bothFormAndProcessableValid ? 'mdi-square-edit-outline' : (onlyOneValid ? 'mdi-progress-question' : 'mdi-gesture-tap')"
              @click="$refs.formModal.open()"
            >
              {{ bothFormAndProcessableValid ? $t('Edit') : onlyOneValid ? $t('Complete') : $t('Fill') }}
            </v-btn>

            <template v-if="$hasRoles(processEditableRoles) && processModel.status">
              <!-- Waiting for Confirmation -->
              <template v-if="processModel.status.match(/waiting_for_confirmation|waiting_for_reaction/) && canAction(processModel.status)">
                <ue-modal
                  ref="promptModal"
                  v-model="promptModalActive"
                  width-type="sm"
                  transition="dialog-bottom-transition"
                  >
                  <template v-slot:activator="{ props }">
                    <v-btn
                      v-bind="{ ...actionButtonProps, ...props }"
                      color="error"
                      prepend-icon="mdi-close-circle-outline"
                    >
                      {{ $t('Reject') }}
                    </v-btn>
                  </template>
                  <template v-slot:body="modalBodyScope">
                    <v-card>
                      <v-card-text>
                        <ue-title padding="b-6" justify="center">
                          {{ getStatusConfigurationValue('rejected', 'dialog_title', $t('Confirm Rejection')) }}
                        </ue-title>
                        <p class="text-body-large mb-4" v-if="getStatusConfigurationValue('rejected', 'dialog_message')">
                          {{ getStatusConfigurationValue('rejected', 'dialog_message') }}
                        </p>
                        <v-textarea
                          v-model="reason"
                          variant="outlined"
                          label="Reason"
                          counter="500"
                          maxlength="500"
                          :rules="[v => !v || v.length <= 500 || $t('Maximum {count} characters allowed', { count: 500 })]"
                          persistent-counter
                          :hint="$t('Press Shift+Enter for a new line, Enter to submit.')"
                          persistent-hint
                          @keydown.enter.exact.prevent="onReasonEnter"
                        />
                      </v-card-text>
                      <v-card-actions class="justify-center">
                        <v-btn
                          :slim="false"
                          color="grey"
                          variant="outlined"
                          :disabled="updating"
                          @click="promptModalActive = false"
                        >
                          {{ getStatusConfigurationValue('rejected', 'dialog_cancel_text', $t('Close')) }}
                        </v-btn>
                        <v-btn
                          color="error"
                          :slim="false"
                          variant="elevated"
                          :disabled="!reason || reason.length > 500"
                          :loading="updating"
                          @click="updateProcess('rejected')"
                        >
                          {{ getStatusConfigurationValue('rejected', 'dialog_confirm_text', $t('Reject')) }}
                        </v-btn>
                      </v-card-actions>
                    </v-card>
                  </template>
                </ue-modal>
                <v-btn
                  v-bind="actionButtonProps"
                  color="success"
                  prepend-icon="mdi-check-circle-outline"
                  :loading="updating"
                  @click="confirmUpdateProcess('confirmed')"
                >
                  {{ $t('Approve') }}
                </v-btn>
              </template>

              <!-- Process Status Actions -->
              <v-btn v-else-if="canAction(processModel.status)"
                v-bind="actionButtonProps"
                :color="nextActionTone"
                :prepend-icon="nextActionIcon"
                :loading="updating"
                :disabled="processModel.status === 'preparing' && !bothFormAndProcessableValid"
                @click="confirmUpdateProcess(nextActionStatus)"
              >
                {{ status.next_action_label }}
              </v-btn>
            </template>
          </div>
        </div>
      </template>
    </v-card>

  </div>
</template>

<script>
import { ref, reactive, computed, toRefs, nextTick, onMounted, onUnmounted, watch } from 'vue'
import _ from 'lodash-es'
import { useI18n } from 'vue-i18n'
import { useStore } from 'vuex'

import { useInput, makeInputProps, makeInputEmits, useAuthorization, useDynamicModal } from '@/hooks'
import { ALERT } from '@/store/mutations'


export default {
  name: 'v-input-process',
  emits: [...makeInputEmits],
  components: {

  },
  props: {
    ...makeInputProps(),
    color: {
      type: String,
      default: 'primary',
    },
    cardVariant: {
      type: String,
      default: 'outlined',
    },
    process: {
      type: Object
    },
    processableTitle: {
      type: String,
      default: 'name'
    },
    processableDetails: {
      type: Array,
      default: () => [],
    },
    schema: {
      type: Object,
    },
    formatters: {
      type: Object,
      default: () => {}
    },
    fetchEndpoint: {
      type: String,
      required: true,
    },
    updateEndpoint: {
      type: String,
      required: true,
    },
    processableEditableRoles: {
      type: [Array, Object],
      default: () => ['superadmin']
    },
    processEditableRoles: {
      type: Array,
      default: () => ['superadmin']
    },
    processableShowStatuses: {
      type: [Array, Object],
      default: () => ['*']
    },
    actionRoles: {
      type: Object,
      default: () => {}
    },
    displayColRatio: {
      type: Array,
      default: () => [5,7]
    },
    historyColRatio: {
      type: Array,
      default: null
    },
    statusConfiguration: {
      type: Object,
      default: () => {},
      value: () => {
        return {
          preparing: {
            title: 'Preparing',
            icon: 'mdi-progress-clock',
            color: 'secondary',
            card_color: 'grey',
            card_variant: 'outlined',
            next_action_label: 'Preparing',
            next_action_color: 'secondary',
            dialog_title: 'Preparing',
            dialog_message: 'The contents are being prepared or updated. Please check back later.',
            dialog_confirm_text: 'Preparing',
            dialog_cancel_text: 'The contents are being prepared or updated. Please check back later.',
            response_message: 'The contents are being prepared or updated. Please check back later.',
          }
        }
      },
    },
    responseLocation: {
      type: String,
      default: 'top',
    },
  },
  setup (props, context) {
    const { t } = useI18n()
    const store = useStore()
    const { hasRoles } = useAuthorization()
    const DynamicModal = useDynamicModal()

    const initializeInput = (val) => {
      return val
    }

    const updateModelValue = (val) => {
      context.emit('update:modelValue', val)
    }

    const Input = useInput(props, { ...context, initializeInput, updateModelValue })

    const processModel = ref(props.process)
    const processableModel = ref(props.process?.processable ?? null)
    const processValid = ref(false)
    const processableValid = ref(false)

    const formatSchema = () => {
      let schema = {}
      let processable = _.get(processModel.value, 'processable', {});

      if(props.schema && __isObject(props.schema)){
        schema = props.schema;

        if(props.formatters && __isObject(props.formatters)){
          for(const [inputName, formatter] of Object.entries(props.formatters)){
            if(schema[inputName]){
              for(const [formattedKey, formatterValues] of Object.entries(formatter)){
                let newFormatterValues = formatterValues;
                if(__isString(formatterValues)){
                  newFormatterValues = formatterValues.split(',');
                }

                let [processableKey, formatterFunction] = newFormatterValues;

                if(__isset(processable[processableKey])){
                  let value = processable[processableKey];

                  if(__isset(value[formatterFunction])){
                    schema[inputName][formattedKey] = value[formatterFunction];
                  }
                }
              }
            }
          }
        }
      }

      return schema;
    }

    const loading = ref(props.process ? false : true)
    const updating = ref(false)

    const UeForm = ref(null)
    const formActive = ref(false)
    const formSchema = ref(props.process ? formatSchema() : null)
    const formIsValid = computed(() => {
      return UeForm.value?.validModel ?? null
    })

    const setSchema = () => {
      formSchema.value = formatSchema()
    }

    const reason = ref('')
    const promptModalActive = ref(false)

    // Enter alone submits the rejection; Shift+Enter inserts a new line (default textarea behavior).
    const onReasonEnter = () => {
      if (!reason.value) return
      if (reason.value.length > 500) return
      if (updating.value) return
      updateProcess('rejected')
    }

    // History reason truncation + popover-on-chevron (per-card, not inline expansion)
    const reasonMenuOpen = ref(false)
    const reasonTextEl = ref(null)
    const hasReasonOverflow = ref(false)
    let reasonResizeObserver = null

    /*
     * Copy-to-clipboard for reason popover — disabled in this version, re-enable later.
     *
     * const reasonCopied = ref(false)
     * let reasonCopyTimer = null
     *
     * const copyReason = async (text) => {
     *   if (!text) return
     *   try {
     *     if (window.navigator.clipboard && window.navigator.clipboard.writeText) {
     *       await window.navigator.clipboard.writeText(text)
     *     } else {
     *       // Fallback for non-secure contexts (e.g. http://app.b2press.test)
     *       const ta = document.createElement('textarea')
     *       ta.value = text
     *       ta.setAttribute('readonly', '')
     *       ta.style.position = 'absolute'
     *       ta.style.left = '-9999px'
     *       document.body.appendChild(ta)
     *       ta.select()
     *       document.execCommand('copy')
     *       document.body.removeChild(ta)
     *     }
     *     reasonCopied.value = true
     *     if (reasonCopyTimer) clearTimeout(reasonCopyTimer)
     *     reasonCopyTimer = setTimeout(() => { reasonCopied.value = false }, 1500)
     *   } catch (e) {
     *     // silently ignore — clipboard may be unavailable
     *   }
     * }
     */

    const checkReasonOverflow = () => {
      if (reasonTextEl.value) {
        hasReasonOverflow.value = reasonTextEl.value.scrollWidth > reasonTextEl.value.clientWidth
      }
    }

    watch(reasonTextEl, (el) => {
      if (reasonResizeObserver) {
        reasonResizeObserver.disconnect()
        reasonResizeObserver = null
      }
      if (el && typeof ResizeObserver !== 'undefined') {
        reasonResizeObserver = new ResizeObserver(checkReasonOverflow)
        reasonResizeObserver.observe(el)
      }
      nextTick(checkReasonOverflow)
    })

    /*
     * Reset copy state whenever popover opens/closes — re-enable with copy feature.
     * watch(reasonMenuOpen, () => {
     *   reasonCopied.value = false
     *   if (reasonCopyTimer) {
     *     clearTimeout(reasonCopyTimer)
     *     reasonCopyTimer = null
     *   }
     * })
     */

    onUnmounted(() => {
      if (reasonResizeObserver) {
        reasonResizeObserver.disconnect()
        reasonResizeObserver = null
      }
      // if (reasonCopyTimer) {
      //   clearTimeout(reasonCopyTimer)
      //   reasonCopyTimer = null
      // }
    })

    const openDialog = (status, callback) => {
      let title = t('Are you sure you want to update the process?')
      let message = t('This action cannot be undone.')

      if(props.statusConfiguration && props.statusConfiguration[status] && props.statusConfiguration[status].dialog_title){
        title = props.statusConfiguration[status].dialog_title
      }else if(processModel.value?.status_dialog_titles && processModel.value.status_dialog_titles[status]){
        title = processModel.value.status_dialog_titles[status]
      }

      if(props.statusConfiguration && props.statusConfiguration[status] && props.statusConfiguration[status].dialog_message){
        message = props.statusConfiguration[status].dialog_message
      }else if(processModel.value?.status_dialog_messages && processModel.value.status_dialog_messages[status]){
        message = processModel.value.status_dialog_messages[status]
      }

      let confirmText = getStatusConfigurationValue(status, 'dialog_confirm_text', t('Yes'))
      let cancelText = getStatusConfigurationValue(status, 'dialog_cancel_text', t('No'))

      DynamicModal.open(null, {
        'modalProps': {
          'widthType': 'md',
          'title': title,
          'description': message,
          'confirmText': confirmText,
          'cancelText': cancelText,
          'titleJustify': 'center',
          'confirmCallback': async () => {
            await callback()
          }
        }
      })
    }

    const fetchProcess = () => {

      if (Input.input.value > 0 && !props.process) {
        const endpoint = props.fetchEndpoint.replace(':id', Input.input.value);

        loading.value = true

        axios.get(endpoint)
          .then(response => {
            loading.value = false
            if(response.status === 200) {

              processModel.value = response.data
              processableModel.value = processModel.value?.processable ?? {}
              setSchema()

              nextTick().then(async () => {
                if(UeForm.value){
                  await UeForm.value.validate()
                  await UeForm.value.VForm.resetValidation()
                  processableValid.value = UeForm.value.validModel
                }
              })
            }
          }).catch(error => {

          }).finally(() => {
            loading.value = false
          })
      }
    }

    const updateProcess = async (status) => {
      if (Input.input.value) {

        if(hasProcessableEditing.value){
          await UeForm.value.validate()
          const isValid = UeForm.value.validModel

          if(!isValid){

            store.commit(ALERT.SET_ALERT, { message: t('Please fill all the required fields'), variant: 'error', location: 'top', timeout: 2000 })

            return
          }
        }

        updating.value = true
        const endpoint = props.updateEndpoint.replace(':id', Input.input.value);

        const data = {
          status: status,
          reason: reason.value,
        }

        axios.put(endpoint, data).then(response => {
          if(response.status === 200) {
            promptModalActive.value = false
            reason.value = ''
            let newStatus = response.data.process_status

            let message = props.statusConfiguration?.[newStatus]?.response_message ?? response.data.message

            store.commit(ALERT.SET_ALERT, {
              message: message,
              variant: response.data.variant,
              location: props.responseLocation
            })

            fetchProcess()
          }
        }).finally(() => {
          updating.value = false
        })
      }
    }

    const getStatusConfigurationValue = (status, key, defaultValue = null) => {
      if(props.statusConfiguration && props.statusConfiguration[status] && props.statusConfiguration[status][key]){
        return props.statusConfiguration[status][key]
      }
      return defaultValue
    }

    const confirmUpdateProcess = async (status) => {
      openDialog(status, () => updateProcess(status))
    }

    const canAction = (status) => {
      return (!props.actionRoles[status] || hasRoles(props.actionRoles[status]))
    }

    const getProcessableEditableRoles = () => {
      let processableEditableRoles = props.processableEditableRoles

      if(__isString(processableEditableRoles) && processableEditableRoles === '*'){
        return ['*']
      }

      if(Array.isArray(processableEditableRoles) && processableEditableRoles.includes('*')){
        return ['*']
      }

      if(Array.isArray(processableEditableRoles)){
        return processableEditableRoles
      }

      if(__isObject(processableEditableRoles)){
        return processableEditableRoles[processModel.value?.status] ?? []
      }

      return []
    }

    const hasProcessableEditing = computed(() => {
      let editingRoles = getProcessableEditableRoles()

      if(Array.isArray(editingRoles) && editingRoles.includes('*')){
        return true
      }

      return hasRoles(editingRoles)
    })

    const canShowProcessableDetails = (model) => {
      let statuses = props.processableShowStatuses

      if(__isString(statuses) && statuses === '*'){
        return true
      }

      if(Array.isArray(statuses) && statuses.includes('*')){
        return true
      }

      if(!model?.status){
        return false
      }

      const activeStatus = model.status

      if(__isObject(statuses)){
        for(const [status, roles] of Object.entries(statuses)){
          if(activeStatus === status){
            if(__isString(roles) && roles === '*'){
              return true
            }

            if(Array.isArray(roles) && roles.includes('*')){
              return true
            }

            if(hasRoles(roles)){
              return true
            }
          }
        }
      } else if(Array.isArray(statuses)){
        if(statuses.includes(model?.status)){
          return true
        }
      }

      return false
    }

    const showProcessableDetails = computed(() => {
      return canShowProcessableDetails(processModel.value)
    })

    const informationalMessage = computed(() => {
      if(processModel.value?.status){
        return processModel.value.status_informational_message
      }

      return t('The contents are being prepared or updated. Please check back later.')
    })

    const states = reactive({
      loading,
      updating,

      UeForm,
      formActive,
      // formSchema,
      formIsValid,

      // processModel,
      processableModel,
      processValid,
      processableValid,

      hasProcessableEditing,
      showProcessableDetails,
      informationalMessage,

      reason,
      promptModalActive,

      title: computed(() => {
        if(processModel.value?.processable){
          return _.get(processModel.value.processable, props.processableTitle, '')
        }

        return null
      }),
      flattenedProcessableDetails: computed(() => {
        const processable = processModel.value?.processable ?? {}

        let details = []

        for(const detail of props.processableDetails){
          if(__isset(detail.field) && __isset(detail.title)) {
            details.push({
              title: t(detail.title),
              value: _.get(processable, detail.field, ''),
            })
          }
        }

        return details
      }),

      bothFormAndProcessableValid: computed(() => {
        return processableValid.value && formIsValid.value
      }),
      onlyOneValid: computed(() => {
        return (processableValid.value && !formIsValid.value) || (!processableValid.value && formIsValid.value)
      }),

      status: computed(() => {
        const status = {
          label: '',
          icon: '',
          color: '',
          card_color: '',
          card_variant: 'outlined',
          next_action_label: '',
          next_action_color: '',
          dialog_title: '',
          dialog_message: '',
          response_message: null,
        }

        if(!processModel.value){
          return status
        }

        let process = processModel.value

        status.label = props.statusConfiguration?.[process.status]?.title ?? process.status_label
        status.icon = props.statusConfiguration?.[process.status]?.icon ?? process.status_icon
        status.color = props.statusConfiguration?.[process.status]?.color ?? process.status_color
        status.card_color = props.statusConfiguration?.[process.status]?.card_color ?? process.status_card_color ?? props.color
        status.card_variant = props.statusConfiguration?.[process.status]?.card_variant ?? process.status_card_variant ?? props.cardVariant
        status.next_action_label = props.statusConfiguration?.[process.status]?.next_action_label ?? process.next_action_label
        status.next_action_color = props.statusConfiguration?.[process.status]?.next_action_color ?? process.next_action_color
        status.dialog_title = props.statusConfiguration?.[process.status]?.dialog_title ?? process.status_dialog_title
        status.dialog_message = props.statusConfiguration?.[process.status]?.dialog_message ?? process.status_dialog_message

        status.response_message = props.statusConfiguration?.[process.status]?.response_message

        return status
      }),

      cardTone: computed(() => {
        const processStatus = processModel.value?.status
        const configuredTone = props.statusConfiguration?.[processStatus]?.tone

        if(configuredTone){
          return configuredTone
        }

        if(/waiting_for_confirmation|waiting_for_reaction/.test(processStatus ?? '')){
          return 'warning'
        }

        return ({ rejected: 'error', confirmed: 'success' })[processStatus] ?? 'neutral'
      }),

      nextActionTone: computed(() => {
        return processModel.value?.status === 'confirmed'
          ? undefined
          : (props.statusConfiguration?.[processModel.value?.status]?.next_action_color ?? processModel.value?.next_action_color)
      }),

      nextActionIcon: computed(() => {
        const processStatus = processModel.value?.status

        return props.statusConfiguration?.[processStatus]?.next_action_icon
          ?? (processStatus === 'confirmed' ? 'mdi-arrow-left-circle-outline' : 'mdi-alert-outline')
      }),

      actionButtonProps: {
        variant: 'outlined',
        size: 'small',
        rounded: 'pill',
        class: 'ue-process-card__action',
      },

      nextActionStatus: computed(() => {
        switch(processModel.value?.status){
          case 'preparing':
            return 'waiting_for_confirmation'
          case 'confirmed':
            return 'preparing'
          default:
            return 'waiting_for_reaction'
        }
      }),
    })

    const methods = reactive({
      setSchema,
      canAction,
      fetchProcess,
      updateProcess,
      confirmUpdateProcess,
      getStatusConfigurationValue,
    })

    watch(
      () => processModel.value?.last_history?.reason,
      () => {
        reasonMenuOpen.value = false
        nextTick(checkReasonOverflow)
      }
    )

    onMounted(() => {
      fetchProcess()
    })

    return {
      ...Input,
      ...toRefs(states),
      formSchema,
      processModel,
      reasonMenuOpen,
      reasonTextEl,
      hasReasonOverflow,
      onReasonEnter,
      // reasonCopied,
      // copyReason,
      ...toRefs(methods),
    }
  },
  watch: {
    input: {
      handler(val, old) {

      },
      immediate: true
    },
    processableModel: {
      handler(val) {
        if(!!val){
          this.$nextTick(() => {
            // __log('processableModel', val)
          })
        }
      },
      immediate: true
    }
  },
  methods: {
    async updateProcessable() {

      if(this.input){

        let payload = {
          // ...this.processableModel,
          ...(this.$lodash.omit(this.UeForm.model, ['id']))
        }

        const endpoint = this.updateEndpoint.replace(':id', this.input);

        const self = this

        axios.put(endpoint, payload).then(response => {
          if(response.data.variant === 'success'){
            self.$store.commit(ALERT.SET_ALERT, { message: response.data.message, variant: response.data.variant })
            if(self.$refs.formModal && typeof self.$refs.formModal.close === 'function'){
              self.$refs.formModal.close()
            }
            self.fetchProcess()
          }
        })
      }


    },
  },
}
</script>

<style lang="sass">
  .v-input-process
    .v-input__control
      height: 100%
</style>

<style lang="scss">
.ue-process-card {
  --ue-process-card-tone: rgba(var(--v-border-color), 0.24);

  border: 1px solid var(--ue-process-card-tone);
  border-radius: 12px;

  &--success { --ue-process-card-tone: rgb(var(--v-theme-success)); }
  &--warning { --ue-process-card-tone: rgb(var(--v-theme-warning)); }
  &--error { --ue-process-card-tone: rgb(var(--v-theme-error)); }

  .ue-process-card__body {
    padding: 16px;
  }

  .ue-process-card__title {
    font-size: 0.875rem;
    font-weight: 600;
    line-height: 1.5rem;
  }

  .ue-process-card__details {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  .ue-process-card__row {
    display: grid;
    grid-template-columns: minmax(0, 2fr) minmax(0, 3fr);
    column-gap: 12px;
    align-items: start;
    font-size: 0.75rem;
    line-height: 1.25rem;
  }

  .ue-process-card__label {
    font-weight: 600;
  }

  .ue-process-card__value {
    min-width: 0;
    overflow-wrap: anywhere;
    color: rgba(var(--v-theme-on-surface), var(--v-medium-emphasis-opacity));
  }

  .ue-process-card__action.v-btn {
    text-transform: none;
    letter-spacing: 0;
  }

  .ue-process-card__action.v-btn--variant-outlined:not(.v-btn--disabled) {
    background-color: color-mix(in srgb, currentColor 10%, transparent);
  }

  .ue-process-card__action.v-btn--disabled {
    opacity: 1;
    border-color: transparent;
    background-color: rgba(var(--v-theme-on-surface), 0.08);
    color: rgba(var(--v-theme-on-surface), 0.45);
  }
}
</style>
