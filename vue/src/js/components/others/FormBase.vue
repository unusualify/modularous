<!--
  FormBase - Refactored form base component.
  Split from CustomFormBase: logic in useFormBaseLogic, field rendering in FormBaseField.
  Same API as CustomFormBase (modelValue, schema, id, row, col, etc.).
-->
<template>
  <v-row
    :id="ctx.id"
    v-bind="ctx.getRow"
    v-resize.quiet="ctx.onResize"
  >
    <slot :name="ctx.getFormTopSlot()" :id="ctx.id" />
    <template v-for="(obj, index) in ctx.flatCombinedArraySorted" :key="index">
      <v-tooltip
        :disabled="!obj.schema.tooltip"
        v-bind="ctx.getShorthandTooltip(obj.schema.tooltip)"
      >
        <template v-slot:activator="{ props: tooltipProps }">
          <v-col
            v-show="!obj.schema.hidden"
            :key="index"
            v-bind="{
              ...ctx.getGridAttributes(obj),
              ...tooltipProps
            }"
            v-intersect="(isIntersecting, entries, observer) => ctx.onIntersect(isIntersecting, entries, observer, obj)"
            v-touch="{
              left: () => ctx.onSwipe('left', obj),
              right: () => ctx.onSwipe('right', obj),
              up: () => ctx.onSwipe('up', obj),
              down: () => ctx.onSwipe('down', obj)
            }"
            v-click-outside="(event) => ctx.onClickOutside(event, obj)"
            :class="ctx.getClassName(obj)"
            :draggable="obj.schema.drag"
            @mouseenter="ctx.onEvent($event, obj)"
            @mouseleave="ctx.onEvent($event, obj)"
            @dragstart="ctx.dragstart($event, obj)"
            @dragover="ctx.dragover($event, obj)"
            @drop="ctx.drop($event, obj)"
          >
            <slot :name="ctx.getTypeTopSlot(obj)" v-bind="{ obj, index, id: ctx.id }" />
            <slot :name="ctx.getKeyTopSlot(obj)" v-bind="{ obj, index, id: ctx.id }" />
            <slot :name="ctx.getTypeItemSlot(obj)" v-bind="{ obj, index, id: ctx.id }">
              <slot :name="ctx.getKeyItemSlot(obj)" v-bind="{ obj, index, id: ctx.id }">
                <FormBaseField
                  :obj="obj"
                  :ctx="ctx"
                  :index="index"
                  :form-item="formItem"
                >
                  <template v-for="(_, name) in $slots" #[name]="slotData">
                    <slot :name="name" v-bind="{ obj, index, id: ctx.id, ...slotData }" />
                  </template>
                </FormBaseField>
              </slot>
            </slot>
            <slot :name="ctx.getTypeBottomSlot(obj)" v-bind="{ obj, index, id: ctx.id }" />
            <slot :name="ctx.getKeyBottomSlot(obj)" v-bind="{ obj, index, id: ctx.id }" />
          </v-col>
        </template>
        <slot :name="ctx.getTooltipSlot()" v-bind="{ obj, index, id: ctx.id }">
          <span>{{ ctx.getShorthandTooltipLabel(obj.schema.tooltip) }}</span>
        </slot>
        <slot :name="ctx.getKeyTooltipSlot(obj)" v-bind="{ obj, index, id: ctx.id }" />
      </v-tooltip>
      <v-spacer v-if="obj.schema.spacer" :key="`s-${index}`" />
    </template>
    <slot :name="ctx.getFormBottomSlot()" :id="ctx.id" />
  </v-row>
</template>

<script>
import { useSlots, onBeforeMount, watch } from 'vue'
import useFormBaseLogic from '@/hooks/useFormBaseLogic'
import FormBaseField from './FormBaseField.vue'

const defaultID = 'form-base'

export default {
  name: 'VFormBase',
  components: { FormBaseField },
  emits: ['update:modelValue', 'update:schema', 'input', 'update', 'resize', 'blur', 'click'],
  props: {
    id: { type: String, default: defaultID },
    rootId: { type: String, default: defaultID },
    row: { type: [Object] },
    rowGroup: { type: [Object] },
    col: { type: [Object, Number, String] },
    colGroup: { type: [Object, Number, String] },
    flex: { type: [Object, Number, String] },
    modelValue: { type: [Object, Array], default: () => null },
    model: { type: [Object, Array] },
    schema: { type: [Object, Array], default: () => ({}) },
    formItem: { type: Object, default: () => ({}) },
    noAutoGenerateSchema: { type: Boolean, default: false }
  },
  setup (props, { emit }) {
    const slots = useSlots()
    const ctx = useFormBaseLogic(props, { emit }, slots)

    onBeforeMount(() => {
      ctx.rebuildArrays(ctx.valueIntern.value ?? {}, ctx.formSchema.value ?? {})
    })

    watch(
      () => props.modelValue,
      (value, oldValue) => {
        if (typeof window.__dot === 'function' && value && oldValue) {
          const newKeys = JSON.stringify(Object.keys(window.__dot(value)))
          const oldKeys = JSON.stringify(Object.keys(window.__dot(oldValue)))
          if (newKeys !== oldKeys) {
            ctx.rebuildArrays(ctx.valueIntern.value, ctx.formSchema.value)
          }
        }
      },
      { deep: true }
    )

    return { ctx }
  }
}
</script>
