<!--
  InputRenderer - Resolves schema type to component and renders it.
  Used by CustomFormBase for the default/component case.
  Register custom types via: import { registerInputType } from '@/components/inputs/registry'
-->
<template>
  <component
    :is="resolvedComponent"
    v-bind="context.bindSchema(obj)"
    :type="context.checkExtensionType(obj)"
    :modelValue="context.setValue(obj)"
    v-model:[context.searchInputSync(obj)]="obj.schema.searchInput"
    @focus="context.onEvent($event, obj)"
    @blur="context.onEvent($event, obj)"
    @[context.suspendClickAppend(obj)]="context.onEvent($event, obj, 'append')"
    @click:append-inner="context.onEvent($event, obj, 'appendInner')"
    @click:prepend="context.onEvent($event, obj, 'prepend')"
    @click:prepend-inner="context.onEvent($event, obj, 'prependInner')"
    @click:clear="context.onEvent($event, obj, 'clear')"
    @click:hour="context.onEvent({ type: 'click' }, obj, 'hour')"
    @click:minute="context.onEvent({ type: 'click' }, obj, 'minute')"
    @click:second="context.onEvent({ type: 'click' }, obj, 'second')"
    @update:modelValue="context.onInput($event, obj)"
    @update:input="context.updateInput($event, obj)"
  >
    <template v-for="s in context.getInjectedScopedSlots(context.id, obj)" v-slot:[s]="slotData">
      <slot :name="context.getKeyInjectSlot(obj, s)" v-bind="{ id: context.id, obj, index: context.index, ...slotData, model: context.valueIntern }" />
    </template>
  </component>
</template>

<script>
import { mapTypeToComponent } from '@/components/inputs/registry'

export default {
  name: 'InputRenderer',
  props: {
    obj: { type: Object, required: true },
    context: {
      type: Object,
      required: true
      // expect: { id, index, valueIntern, bindSchema, setValue, onInput, onEvent, updateInput,
      //           checkExtensionType, searchInputSync, suspendClickAppend, getInjectedScopedSlots, getKeyInjectSlot }
    }
  },
  computed: {
    resolvedComponent () {
      return mapTypeToComponent(this.obj.schema.type, this.context.vueInstance?.components ?? {})
    }
  }
}
</script>
