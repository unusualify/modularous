<template>

    <!-- TREEVIEW -->
    <v-treeview
        v-model="input"
        v-model:active="input"
        v-model:open="obj.schema.open"
        v-bind="obj.schema"
        :items="obj.schema.items"

    >
        <slot></slot>
        <!-- works with #[s]="slotData" " -->
        <!-- <template v-for="s in getInjectedScopedSlots(id, obj)" #[s]="slotData"><slot :name="getKeyInjectSlot(obj, s)" v-bind= "{ id, obj, index,  ...slotData}" /></template> -->
    </v-treeview>
    <!-- END TREEVIEW -->
</template>

<script>
import { useInput, makeInputEmits } from '@/hooks'

export default {
  name: 'v-input-treeview',
  emits: [...makeInputEmits],
  setup (props, context) {
    return { ...useInput(props, context) }
  }
}
</script>
