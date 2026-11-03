<template>
  <v-stepper
    :model-value="activeStep"
    @update:model-value="goStep"
    color="prima"
    :class="[
      'ue-stepper-form ue-stepper--no-background fill-height flex-column',
      flexBreakpoint ? `d-${flexBreakpoint}-flex` : 'd-flex'
    ]"
    :mobile-breakpoint="`md`"
  >
    <template v-slot:default="{ prev, next }">
      <StepperHeader
        :forms="forms"
        :active-step="activeStep"
        :summary-title="summaryStepTitle"
        @step-click="goStep"
      />

      <v-row class="mt-3 flex-fil">
        <!-- left side -->
        <v-col v-bind="formCol" v-fit-grid class="order-md-1 order-2">
          <StepperContent
            v-model="models"
            :schemas="schemas"
            @update:schemas="schemas = $event"

            :forms="forms"
            :active-step="activeStep"
            :form-refs="formRefs"
            :is-editing="isEditing"

            @form-input="handleInput($event)"
            @form-valid="updateFormValid($event)"

            max-height="100vh"
            :cover-height="calculateHeights"
            :transparent="isLastStep || forms[activeStep - 1]?.transparentBody === true"
          >
            <template #preview>
              <StepperPreview v-if="!lastFormPreviewLoading && isLastStep"
                :formatted-preview="formattedPreview"

                :preview-form-data="lastFormPreview"
                :last-step-model="lastStepModel"
                :protected-last-step-model="protectedLastStepModel"
                :final-form-title="finalFormTitle"
                :final-form-subtitle="finalFormSubtitle"
                @final-form-action="handleFinalFormAction"
              />
              <div v-else-if="lastFormPreviewLoading && isLastStep"
                class="d-flex align-center justify-center"
                style="height: 70vh;"
              >
                <v-progress-circular model-value="20" indeterminate/>
              </div>
            </template>

            <!-- <template v-if="activeStepIsFullWidth" #bottom>
              <div class="d-flex justify-space-between">
                <v-btn color="secondary" @click="goStep(activeStep - 1)">
                  {{ $t('previous').toUpperCase() }}
                </v-btn>
                <v-btn color="secondary" @click="goStep(activeStep + 1)">
                  {{ $t('next').toUpperCase() }}
                </v-btn>
              </div>
            </template> -->
          </StepperContent>
        </v-col>

        <!-- right side -->
        <v-col v-bind="summaryCol" class="order-md-2 order-1">
          <StepperSummary
            :is-last-step="isLastStep"
            :forms="forms"
            :active-step="activeStep"
            :models="models"
            :schemas="schemas"
            :preview-model="previewModel"
            :preview-titles="previewTitles"
            :is-preview-model-filled="isPreviewModelFilled"
            @next-form="nextForm"
            @complete-form="completeForm"
          >
            <template v-for="(slot, slotName) in summaryFormScopes" :key="`slot-${i}`" v-slot:[`${slotName}`]="summaryFormScope">
              <slot :name="slotName" v-bind="summaryFormScope">

              </slot>
            </template>

            <template #summary.final="{ onComplete }">
              <slot name="summary.final"
                v-bind="{
                  model: models,
                  schema: schemas,
                  previewModel: previewModel,
                  completeForm: completeForm,
                }"
              >
                <StepperFinalSummary
                  :loading="loading"
                  :is-completed="isCompleted"
                  @complete="onComplete"
                >
                  <template v-if="$slots['summary.final.body']" v-slot:body>
                    <slot name='summary.final.body' v-bind="{
                      models,
                      schemas,
                      lastStepModel,
                      finalFormFields,
                      lastFormPreview,
                    }">

                    </slot>
                  </template>
                  <template v-slot:total.label>
                    <slot name='summary.final.total.label'>
                      {{ $t('Total').toUpperCase() }}
                    </slot>
                  </template>
                  <template v-slot:total>
                    <slot name='summary.final.total' v-bind="{payload: this.payload}">
                      <!-- <ue-text-display class="text-headline-small text-white" text="$2500" subText="+ {{ $t('VAT') }}" /> -->
                    </slot>
                  </template>
                  <template v-slot:description>
                    <slot name="summary.final.description">
                      At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium iusto odio
                    </slot>
                  </template>
                </StepperFinalSummary>
              </slot>
            </template>
          </StepperSummary>
        </v-col>
      </v-row>

      <div v-if="!isLastStep" class="ue-stepper-form__actions flex-shrink-0 mt-3">
        <v-divider />
        <div class="d-flex justify-end align-center ga-4 pt-3">
          <v-btn
            variant="text"
            class="text-capitalize"
            :disabled="activeStep === 1"
            @click="goStep(activeStep - 1)"
          >
            {{ $t('Previous') }}
          </v-btn>
          <v-btn
            color="primary"
            variant="flat"
            rounded="pill"
            class="text-capitalize px-8"
            :disabled="$hasRequestInProgress()"
            @click="nextForm(activeStep - 1)"
          >
            {{ $t('Next') }}
          </v-btn>
        </div>
      </div>


      <ue-modal
        ref="modal"
        v-model="modalActive"
        :width-type="'md'"
        :description="modalMessage"
        persistent

        v-bind="responseModalOptions"
      >
        <template v-slot:body.description>
          <div class="d-flex flex-column align-center justify-center">
            <v-icon size="64" color="success" class="mb-4">{{ responseModalIcon }}</v-icon>
            <h2 class="text-headline-large mb-4 text-primary">{{ responseModalTitle }}</h2>
            <p class="text-body-large text-medium-emphasis text-center" style="white-space: pre-line;">{{ responseModalMessage }}</p>
          </div>
        </template>
        <template v-slot:body.options>
          <div class="d-flex justify-center w-100">
            <v-btn variant="flat" color="primary" @click="completed">
              {{ responseModalButtonText }}
            </v-btn>
          </div>
        </template>
      </ue-modal>

    </template>

  </v-stepper>
</template>

<script>
  import { toRefs, reactive, ref, computed } from 'vue';
  import { useGoTo } from 'vuetify'
  import { map, reduce, find, each, filter, get, isEqual, uniq, isBoolean, cloneDeep, isString, isObject } from 'lodash-es';

  import { getModel } from '@/utils/getFormData.js'
  import { handleMultiFormEvents } from '@/utils/formEvents'

  import { useInputHandlers, useValidation, useCastAttributes } from '@/hooks'
  import api from '@/store/api/form'

  import NotationUtil from '@/utils/notation';

  import StepperHeader from '__components/stepper/StepperHeader.vue'
  import StepperContent from '__components/stepper/StepperContent.vue'
  import StepperSummary from '__components/stepper/StepperSummary.vue'
  import StepperPreview from '__components/stepper/StepperPreview.vue'
  import StepperFinalSummary from '__components/stepper/StepperFinalSummary.vue'

  export default {
    name: 'StepperForm',
    components: {
      StepperHeader,
      StepperContent,
      StepperSummary,
      StepperPreview,
      StepperFinalSummary
    },
    props: {
      flexBreakpoint: {
        type: String,
        default: null,
        validator: (value) => {
          return ['sm', 'md', 'lg', 'xl', 'xxl'].includes(value)
        }
      },
      forms: {
        type: Object,
        default () {
          return []
          // example
          // [
          //   title: {stepper_item_title},,
          //   schema: v-custom-form-base.schema
          // ]
        }
      },
      actionUrl: {
        type: String,
      },
      modelValue: {
        type: Object,
        default: () => {
          return {
          }
        }
      },
      redirectUrl: {
        type: String,
        default: null
      },
      preview: {
        type: Array,
        default: []
      },
      currentStep: {
        type: Number,
        default: 1
      },
      cardsNotation: {
        type: String,
        default: 'models.1.pressReleasePackages'
      },
      summaryNotations: {
        type: [Array, Object],
        default: () => {
          return {}
        }
      },
      previewNotations: {
        type: [Array, Object],
        default: () => {
          return []
        }
      },
      isEditing: {
        type: Boolean,
        default: false
      },
      finalFormTitle: {
        type: String,
        default: null
      },
      finalFormSubtitle: {
        type: String,
        default: null
      },
      summaryStepTitle: {
        type: String,
        default: null
      },
      finalFormFields: {
        type: Array,
        default: () => {
          return []
        }
      },
      validationScrollingDuration: {
        type: Number,
        default: 1000
      },
      validationScrollingEasing: {
        type: String,
        default: 'easeInOutCubic'
      },
      validationScrollingOffset: {
        type: Number,
        default: 0
      },
      protectInitialValue: {
        type: Boolean,
        default: false
      },
      responseModalIcon: {
        type: String,
        default: 'mdi-check-circle-outline'
      },
      responseModalTitle: {
        type: String,
        default: () => {
          return this.$t('Request Complete')
        }
      },
      responseModalMessage: {
        type: String,
        default: () => {
          return this.$t('Congratulations! Your request was completed successfully.')
        }
      },
      responseModalButtonText: {
        type: String,
        default: 'Ok'
      },
      responseModalOptions: {
        type: Object,
        default: () => {
          return {}
        }
      }
    },
    setup (props, context) {
      const inputHandlers = useInputHandlers()
      const validations = useValidation(props)
      const {castAttribute} = useCastAttributes()
      const goTo = useGoTo()

      const stepperActionRef = ref(null)
      const loading = ref(false)

      const state = reactive({
        stepperActionRef,
        loading,
      })

      const formRefs = computed(() => map(props.forms, (m,i) => ref(null) ))

      return {
        ...inputHandlers,
        ...validations,
        ...toRefs(state),
        formRefs,
        goTo,
        castAttribute
      }
    },
    data () {
      return {
        activeStep: this.currentStep,
        modalActive: false,
        modalMessage: '',
        isCompleted: false,
        lastFormPreviewLoading: false,
        schemas: [],
        models: [],
        valids: [],
        lastFormPreview: [],
        lastStepModel: {},
        protectedLastStepModel: {},
        previewModel: [],
        pendingHandleFunctions: [],
        isNavigatingSteps: false,
      }
    },
    methods: {
      completed(){
        this.modalActive = false
        if(this.redirectUrl){
          window.location.href = this.redirectUrl
        }
      },
      completeForm (){
        const method = this.payload?.id ? 'put' :'post'
        const self = this

        this.loading = true

        api[method](this.actionUrl, this.payload, function (response) {

          if(response.status === 200 && response.data.variant === 'success'){
            self.loading = false
            self.isCompleted = true
            self.modalMessage = response.data.message
            self.modalActive = true
          }else{
            self.loading = false
            this.$store.commit(ALERT.SET_ALERT,  response.data)
          }
          // redirector(response.data)

          // if (callback && typeof callback === 'function') callback(response.data)
        }, function (response) {
          self.loading = false
          __log(response)
          // if (errorCallback && typeof errorCallback === 'function') errorCallback(response.data)
        })
      },
      isPreviewModelFilled(index){
        const model = this.previewModel[index] ?? {}
        let isFilled = false

        for(const name in model){
          if(!!model){
            isFilled = true
            break
          }
        }

        return isFilled
      },
      handleInput (payload) {
        const { event, index } = payload
        const { on, key, obj, value } = event
        if (on === 'input' && !!key) {
          // if (!this.serverValid) {
          //   this.resetSchemaError(key)
          // }
          // this.handleEvent(obj)
          let availableValue = get(this.models[index], key)
          if(JSON.stringify(availableValue) !== JSON.stringify(value)){
            this.pendingHandleFunctions.push((models, schemas, previewModel) => {
              handleMultiFormEvents(models, schemas, obj.schema, index, previewModel)
            })
          }else{
            handleMultiFormEvents(this.models, this.schemas, obj.schema, index, this.previewModel)
          }

        }
      },
      updateFormValid(payload) {
        const { event, index } = payload
        this.valids[index] = event
      },
      async goStep(step){
        if(step <= this.activeStep){
          this.activeStep = step
          return
        }

        if(this.isNavigatingSteps){
          return
        }

        // Cached valid flags miss inputs still being loaded by requests, so each form is validated in order
        this.isNavigatingSteps = true
        try {
          while(this.activeStep < step){
            const index = this.activeStep - 1
            await this.waitForPendingRequests()
            await this.validateForm(index)

            if(this.formRefs[index].value[0].validModel !== true){
              return
            }

            this.activeStep += 1
            await this.$nextTick()
          }
        } finally {
          this.isNavigatingSteps = false
        }
      },
      waitForPendingRequests() {
        const isRequestInProgress = () => this.$store.state.config.isRequestInProgress ?? false

        return new Promise((resolve) => {
          if(!isRequestInProgress()){
            resolve()
            return
          }

          const stop = this.$watch(isRequestInProgress, (inProgress) => {
            if(!inProgress){
              stop()
              resolve()
            }
          })
        })
      },
      async nextForm(index) {
        await this.validateForm(index)

        if(this.formRefs[index].value[0].validModel === true){
          this.activeStep += 1
        }else if(index < this.forms.length) {
          await this.validateForm(index)
        }
      },
      async validateForm(i) {
        const formRef = this.formRefs[i]
        const form = formRef.value[0]
        form.manualValidation = true

        const result = await form.validate()
        form.manualValidation = false

        if(isBoolean(result.valid) && result.valid === false){
          this.$nextTick(() => {
              const el = this.$el.querySelector(".v-input--error .v-input__details[role='alert'] .v-messages");
              if(el){
                let easings = [
                  'linear',
                  'easeInQuad',
                  'easeOutQuad',
                  'easeInOutQuad',
                  'easeInCubic',
                  'easeOutCubic',
                  'easeInOutCubic',
                  'easeInQuart',
                  'easeOutQuart',
                  'easeInOutQuart',
                  'easeInQuint',
                  'easeOutQuint',
                  'easeInOutQuint',
                ]

                this.goTo(el, {
                  container: '#ue-stepper-content-window',
                  duration: this.validationScrollingDuration,
                  offset: this.validationScrollingOffset,
                  easing: this.validationScrollingEasing,
                });
              }
          });
        }

        return result
      },
      handleFinalFormAction({index, event}) {
        // const data = this.previewFormData[index]
        const data = this.lastFormPreview[index]
        const fieldName = data.fieldName
        const fieldFormat = data._fieldFormat ?? 'id'
        const lastStepModel = cloneDeep(this.lastStepModel)
        let fieldArray = this.lastStepModel[fieldName] ?? [];

        // Check if the id exists in the array and toggle it
        if(isString(fieldFormat)){
          lastStepModel[fieldName] = fieldArray.includes(data[fieldFormat])
            ? fieldArray.filter((id) => id !== data[fieldFormat])
            : [...fieldArray, data[fieldFormat]];
        }else if(isObject(fieldFormat)){
          const fieldFormatUniqueKey = data._fieldFormatUniqueKey ?? 'id'
          const fieldFormatSourceKey = data._fieldFormatSourceKey ?? 'id'

          if(event === false){
            for(const key in fieldArray){
              let value = fieldArray[key]
              if(isObject(value)){
                if(event === false){
                  if(value[fieldFormatUniqueKey] === data[fieldFormatSourceKey]){
                    fieldArray.splice(key, 1)
                    break
                  }
                }
              }
            }
          } else if(event === true){
            let itemPayload = {}

            for(const field in fieldFormat){
              const key = fieldFormat[field]
              itemPayload[field] = get(data, key)
            }

            fieldArray.push(itemPayload)
          }

          lastStepModel[fieldName] = fieldArray

        }

        // Force reactivity by creating a new reference
        this.lastStepModel = lastStepModel;
      },
      async handlePreviewFormField(formField){

        if(!formField.modelNotation)
          return

        let modelNotation = formField.modelNotation
        let formFieldKey = `preview-form-field-${modelNotation}`
        let cacheFormFieldValuesKey = `preview-form-field-values-${modelNotation}`

        this.lastFormPreviewLoading = true
        let formFieldValues = this.$cacheGet(formFieldKey, {})

        // get cached form field values
        let cachedFormFieldValues = reduce(formFieldValues, (acc, value, key) => {
          acc.push(parseInt(key))
          return acc
        }, [])

        // get cached form field fetched values
        let cachedFormFieldFetched = this.$cacheGet(cacheFormFieldValuesKey, [])
        let modelValue = get(this.models, modelNotation)

        if(modelValue && formField.endpoint){ // if model value is present and endpoint is present fetch new items

          this.lastFormPreview = []

          let currentIds = Array.isArray(modelValue)
            ? modelValue
            : [modelValue]

          let newIds = currentIds.filter(id => !cachedFormFieldValues.includes(id))
          let cachedIds = currentIds.filter(id => cachedFormFieldValues.includes(id))

          if(cachedIds.length > 0){ // get cached items acc. to form_field_id
            cachedIds.forEach(id => {
              let cachedItems = cachedFormFieldFetched.filter(item => item.form_field_id === id && item.form_field_notation === modelNotation)
              cachedItems.forEach(item => {
                this.lastFormPreview.push(item)
              })
              formFieldValues[id] = true
            })
          }

          if(newIds.length > 0){ // fetch new items
            if(!formField.endpoint){
              return
            }

            let endpoint = window.__addParametersToUrl(formField.endpoint, {ids: newIds})

            let response = await axios.get(endpoint)

            if(response.status === 200){
              let items = response.data

              items.forEach(item => {
                formFieldValues[item.id] = true
              })

              if(formField.notation){
                items = items.map(item => {
                  let value = get(item, formField.notation, [])

                  let formCardFields = formField.cardFields ?? ['name']
                  let fieldName = formField.fieldName || modelNotation.split('.').pop()

                  if(Array.isArray(value)){
                    value = value.map(subItem => {
                      return {
                        ...subItem,
                        isSelected: false,
                        fieldName: fieldName,
                        form_field_notation: modelNotation,
                        form_field_id: item.id,
                        form_card_items: formCardFields.map(cardField => {
                          if(Array.isArray(cardField)){
                            return cardField.map(cardItem => {
                              return subItem[cardItem] ?? 'N/A'
                            })
                          }else{
                            return subItem[cardField] ?? 'N/A'
                          }
                        }),

                        _fieldFormat: formField.format ?? 'id',
                        _fieldFormatSourceKey: formField.formatSourceKey ?? 'id',
                        _fieldFormatUniqueKey: formField.formatUniqueKey ?? 'id',
                      }
                    })
                  } else { // is object
                    value = {
                      ...value,
                      ...{
                        isSelected: false,
                        fieldName: fieldName,
                        form_field_notation: modelNotation,
                        form_field_id: item.id,
                        form_card_items: formCardFields.map(cardField => {
                          if(Array.isArray(cardField)){
                            return cardField.map(cardItem => {
                              return value[cardItem] ?? 'N/A'
                            })
                          }else{
                            return value[cardField] ?? 'N/A'
                          }
                        }),
                        _fieldFormat: formField.format ?? 'id',
                        _fieldFormatSourceKey: formField.formatSourceKey ?? 'id',
                        _fieldFormatUniqueKey: formField.formatUniqueKey ?? 'id',
                      }
                    }
                  }
                  return value
                })
              } else {
                items = items.map(item => {
                  return {
                    ...item,
                    form_field_notation: formField.notation,
                    form_field_id: item.id
                  }
                })
              }

              let flattenedItems = reduce(items, (acc, item) => {
                if(Array.isArray(item)){
                  return [...acc, ...item]
                } else {
                  return [...acc, item]
                }
              }, [])

              flattenedItems.forEach(item => {
                this.$cachePush(cacheFormFieldValuesKey, item)
                this.lastFormPreview.push(item)
              })
            }
          }

          this.$cachePut(formFieldKey, formFieldValues)

        }

        this.lastFormPreviewLoading = false
      }
    },
    computed: {
      disabled () {
        return this.activeStep === 1 ? 'prev' : this.activeStep === this.forms.length ? 'next' : undefined
      },
      previewTitles() {
        return map(this.models, (model, index) => {
          let form = this.forms[index]
          let schema = this.schemas[index]
          let summarySearchHaystack = form['summarySearchHaystack'] ?? 'model'
          let summarySearchInput = form['summarySearchInput'] ?? null

          let title = form['summaryTitle'] ?? form['previewTitle'] ?? form['title']
          let castedTitle = this.$castValueMatch(title, model )

          try {
            if(summarySearchHaystack === 'schema' && summarySearchInput){
              let parts = title.split('.')

              let newParts = []
              for(const j in parts){
                let part = parts[j]
                if(part === '*' ){
                  let _id = model[summarySearchInput]
                  newParts.push(`*id=${_id}`)
                }else{
                  newParts.push(part)
                }
              }
              let notation = newParts.join('.')
              castedTitle = this.castAttribute(notation, schema )
            }
          } catch (error) {
            console.error(error, {
              'summarySearchHaystack': summarySearchHaystack,
              'summarySearchInput': summarySearchInput,
              'title': title,
              'model': model,
              'schema': schema,
            })
          }

          return castedTitle
        })
      },
      isLastStep(){
        return this.activeStep > this.forms.length
      },
      summaryCardModels(){
        return __data_get(this, this.cardsNotation, [])
      },
      displayInfo(){
        let data = []
        for(const index in this.schemas){
          data[index] = this.$getDisplayData(this.schemas[index], this.models[index])
        }
        return data
      },
      formattedPreview(){
        return NotationUtil.formattedPreview(this.displayInfo, this.previewNotations)
      },
      payload(){
        let model = reduce(this.models, function(acc, model, index){
          return {...acc, ...model}
        }, {})

        let lastFormPreview = this.lastFormPreview

        let lastStepModel = reduce(this.lastStepModel, function(acc, value, key){
          if(Array.isArray(value)){
            value = value.reduce((acc, element) => {
              let item = isObject(element)
                ? lastFormPreview.find((previewItem) => previewItem[previewItem._fieldFormatSourceKey ?? 'id'] === element[previewItem._fieldFormatUniqueKey ?? 'id'] && previewItem.fieldName === key)
                : lastFormPreview.find((previewItem) => previewItem.id === element && previewItem.fieldName === key)

              if(item){
                acc.push(element)
              }
              return acc
            }, [])
            acc[key] = value
          }else{
            acc[key] = value
          }
          return acc
        }, {})

        return {
          ...model,
          ...lastStepModel,
        }
      },
      summaryFormScopes(){
        return reduce(this.$slots, (acc, slot, slotName) => {
            if(slotName.match(/summary-form-\d+/)){
              acc[slotName] = slot
            }
          return acc
        }, {})
      },
      activeStepIsFullWidth(){
        if(!this.forms[this.activeStep - 1]){
          return false
        }

        return this.forms[this.activeStep - 1]?.fullWidth === true ?? false
      },
      formCol(){
        let defaultCol = {
          cols: 12,
          lg: 8,
          md: 8,
        }
        return this.activeStepIsFullWidth ? {
          cols: 12,
          lg: 12,
          md: 12,
        } : defaultCol
      },
      summaryCol(){
        let defaultCol = {
          cols: 12,
          lg: 4,
          md: 4,
        }
        return this.activeStepIsFullWidth  ? {
          class: 'd-none'
        } : defaultCol
      },
      calculateHeights(){
        let headerHeight = 80
        let layoutPaddingHeight = 48
        let stepperActionsHeight = 65
        let topToolbarHeight = 64
        let height = headerHeight + layoutPaddingHeight

        if(!this.isLastStep){
          height += stepperActionsHeight
        }

        if(this.$vuetify.display.mobile){
          height += topToolbarHeight
        }

        return height
      }
    },
    watch: {
      schemas: {
        handler (value, oldValue) {
          if(!isEqual(value, this.schemas)){
            this.schemas = value
          }
        },
        deep: true
      },
      models: {
        handler (newVal, oldVal) {
          if(this.pendingHandleFunctions.length > 0){
            this.pendingHandleFunctions.forEach((fn) => {
              if(typeof fn === 'function'){
                fn(this.models, this.schemas, this.previewModel)
              }
            })
            this.pendingHandleFunctions = []
          }
          this.models.forEach((model, index) => {
            if(!!this.previewModel[index]){
              Object.keys(this.previewModel[index]).forEach((key) => {
                if(!this.models[index][key]){
                  delete this.previewModel[index][key]
                }
              })
            }
          })
        },
        deep: true
      },
      activeStep: {
        handler (value, oldValue) {

          this.finalFormFields.forEach((formField) => {
            if(formField.afterStep && formField.afterStep === oldValue){
              this.handlePreviewFormField(formField)
            }
          })

        },
        deep: true
      }
    },
    created() {
      // NotationUtil.test()

      let self = this

      this.forms.forEach((form, index) => {
        let schema = form.schema

        let model = getModel(schema, this.modelValue)

        self.models.push(model)
        self.schemas.push(self.invokeRuleGenerator(schema, model))
        self.valids.push(null)
      })
      this.previewModel = this.preview

      this.lastStepModel = reduce(this.finalFormFields, (acc, finalFormField, key) => {
        let fieldName = null

        if(__isObject(finalFormField)){
          fieldName = finalFormField.inputName || finalFormField.fieldName || finalFormField.modelNotation.split('.').pop()
        }else{
          fieldName = finalFormField.split('.').pop()
        }

        if(!__isset(acc[fieldName])){
          acc[fieldName] = []
        }
        return acc
      }, {})

      this.lastStepModel = this.$lodash.mapValues(this.lastStepModel, (data, key) => {
        if(this.modelValue[key]){
          return this.modelValue[key]
        }
        return data
      })

      if(this.protectInitialValue){
        this.protectedLastStepModel = cloneDeep(this.lastStepModel)
      }

    }
  }
</script>

<style lang="sass">

  .ue-stepper-form
    &.v-sheet.ue-stepper--no-background
      background-color: transparent !important
      color: currentColor !important
      box-shadow: unset
      border-radius: 0

      .v-stepper-header, .ue-stepper-form__body
        background: rgb(var(--v-theme-surface))

      > .v-row
        flex: 0 1 auto
        min-height: 0

        @media (min-width: 960px)
          flex-wrap: nowrap

        &:has(.v-window-item--active .ue-stepper-form__card--scroll)
          flex-grow: 1

      .ue-stepper-form__body
        border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity))
        border-radius: 12px

        > #ue-stepper-content-window
          flex: 1 1 auto
          min-height: 0

        &--transparent
          background: transparent
          border-color: transparent !important

          &:has(.v-window-item--active .ue-stepper-form__card--scroll) > #ue-stepper-content-window
            flex: 1 1 0
            max-height: none !important
            container-type: size

          .ue-stepper-form__card--scroll
            max-height: calc(100cqh - var(--ue-stepper-card-offset, 144px))

          .v-row:has(> .ue-stepper-form__segment)
            row-gap: 0

          .ue-stepper-form__segment
            background: rgb(var(--v-theme-surface))
            border-inline: 1px solid rgba(var(--v-border-color), var(--v-border-opacity))
            padding-inline: 24px !important

            &:not(&--top)
              padding-top: 8px

            &--top
              border-top: 1px solid rgba(var(--v-border-color), var(--v-border-opacity))
              border-top-left-radius: 12px
              border-top-right-radius: 12px
              padding-top: 24px !important

            &--bottom
              border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity))
              border-bottom-left-radius: 12px
              border-bottom-right-radius: 12px
              padding-bottom: 24px !important

            &:has(> .d-none)
              display: none

          .ue-stepper-form__gap
            padding: 8px 0 !important

            > *
              display: none

          .ue-form
            background: transparent

            .v-row.py-4
              padding-bottom: 0 !important

      .ue-stepper-form__card
        background: rgb(var(--v-theme-surface))
        border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity))
        border-radius: 12px
        padding: 8px 16px

        &--scroll
          max-height: calc(var(--ue-stepper-content-height) - var(--ue-stepper-card-offset, 160px))
          overflow-y: auto

      .v-stepper-window
        margin: 0

    .ue-avatar--border25, .ue-stepper-item__icon--border25 .v-avatar
      border-radius: 25%

    .ue-stepper-form__preview
      // height: 100% !important

    .v-card-item, .v-card-text
      // padding-left: 12 * $spacer
      // padding-right: 12 * $spacer

    .ue-stepper-form__summary-final
      // background-color: $stepper-form-summary-final-background

    .ue-stepper-form__preview-bottom
      display: flex !important
      flex-direction: row-reverse !important
      // padding-left: 12 * $spacer
      // padding-right: 12 * $spacer

    .ue-stepper-form__preview-bottom
      // padding-top: 12 * $spacer
    .ue-stepper-form__preview-bottom
      // padding-bottom: 12 * $spacer



</style>
