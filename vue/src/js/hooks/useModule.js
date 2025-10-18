import { ref, reactive, computed, onMounted, toRefs, getCurrentInstance } from 'vue'
import { useI18n } from 'vue-i18n'
import _ from 'lodash-es'

import { propsFactory } from 'vuetify/lib/util/propsFactory.mjs'

export const makeModuleProps = propsFactory({
  name: {
    type: String
  },
  customTitle: {
    type: String
  },
  titlePrefix: {
    type: String,
    default: ''
  },
  titleKey: {
    type: String,
    default: 'name'
  },
  fillHeight: {
    type: Boolean,
    default: false,
  },
  slots: {
    type: Object,
    default () {
      return {}
    }
  },
  noFullScreen: {
    type: Boolean,
    default: false
  },
  endpoints: {
    type: Object,
    default : {}
  },
  items: {
    type: Array,
    default: []
  }
})

export default function useModule (props, context) {

  const { t, te } = useI18n({ useScope: 'global' })
  let loading = ref(false)

  const moduleSnakeName = _.snakeCase(props.moduleName ?? props.name)
  const routeSnakeName = _.snakeCase(props.routeName ?? props.name)
  const snakeName = _.snakeCase(props.name)

  const tableTranslationNotation = props.isModuleRoute
    ? [
        'modules',
        ...(props.moduleName ? [moduleSnakeName] : []),
        ...(props.routeName ? [routeSnakeName] : []),
        'name',
      ].join('.')
    : [
        'modules',
        ...(props.name ? [snakeName] : []),
      ].join('.')

  const state = reactive({
    moduleSnakeName,
    routeSnakeName,
    snakeName,
    tableTranslationNotation,

    transNameSingular: computed(() => te(tableTranslationNotation, 0)
      ? t(tableTranslationNotation, 0)
      : (props.isModuleRoute ? props.routeName : props.name)
    ),
    transNamePlural: computed(() => t(tableTranslationNotation, 1)),
    permissionName: computed(() => _.kebabCase(props.isModuleRoute ? props.routeName : props.name)),


    // snakeName: _.snakeCase(props.name),
    // transNameSingular: computed(() => te('modules.' + state.snakeName, 0)
    //   ? t('modules.' + state.snakeName, 0)
    //   : props.name
    // ),
    // transNamePlural: computed(() => t('modules.' + state.snakeName, 1)),
    // permissionName: _.kebabCase(props.name),
    // // transNameCountable: computed(() => t('modules.' + state.snakeName, getters.totalElements.value)),
    // moduleTitle: computed(() => {
    //   const prefix = props.titlePrefix ? props.titlePrefix : ''
    //   return prefix + (__isset(props.customTitle) ? props.customTitle : state.transNamePlural)
    // }),
    windowSize: {x: 0,y: 0},
    elements: props.items,
    searchPlaceholder: t("Type to Search"),
    searchModel: '',
  })

  const methods = reactive({
    onResize () {
      state.windowSize = { x: window.innerWidth, y: window.innerHeight }
    },

  })

  return {
    ...toRefs(state),
    ...toRefs(methods)
  }
}
