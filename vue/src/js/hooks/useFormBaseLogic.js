/**
 * useFormBaseLogic - Composable with form base logic extracted from CustomFormBase.
 * Used by FormBase.vue (refactored version).
 */
import { getCurrentInstance, ref, computed, watch } from 'vue'
import { get, isPlainObject, isFunction, isString, isNumber, isEmpty, orderBy, delay, find, findIndex, omit, cloneDeep } from 'lodash-es'
import formEvents from '@/utils/formEvents'
import { mapTypeToComponent as registryMapTypeToComponent } from '@/components/inputs/registry'

const orderDirection = 'ASC'
const pathDelimiter = '.'
const classKeyDelimiter = '-'
const defaultID = 'form-base'
const isPicker = 'date|time|color'
const onEventDelay = 1

const mouse = 'mouseenter|mouseleave'
const change = 'input|click'
const watchEvents = 'focus|input|click|blur'
const display = 'resize|swipe|intersect'

const topAppendix = 'top'
const bottomAppendix = 'bottom'
const slotAppendix = 'slot'
const tooltipAppendix = 'tooltip'
const injectAppendix = 'inject'
const itemClassAppendix = 'item'
const typeClassAppendix = 'type'
const keyClassAppendix = 'key'
const arrayClassAppendix = 'array'
const propertyClassAppendix = 'prop'

const injectSlotAppendix = `${slotAppendix}-${injectAppendix}`
const topSlotAppendix = `${slotAppendix}-${topAppendix}`
const itemSlotAppendix = `${slotAppendix}-${itemClassAppendix}`
const bottomSlotAppendix = `${slotAppendix}-${bottomAppendix}`
const tooltipSlotAppendix = `${slotAppendix}-${tooltipAppendix}`

const rowDefault = { noGutters: false }
const rowGroupDefault = { noGutters: false, class: 'my-2' }
const colDefault = { cols: 'auto' }
const dropEffect = 'move'

const defaultSchemaIfValueIsNullOrUndefined = key => ({ type: 'text', label: key })
const defaultSchemaIfValueIsString = key => ({ type: 'text', label: key })
const defaultSchemaIfValueIsNumber = key => ({ type: 'number', label: key })
const defaultSchemaIfValueIsBoolean = key => ({ type: 'checkbox', label: key })
const defaultPickerSchemaText = { type: 'text', readonly: true }
const defaultPickerSchemaMenu = { closeOnContentClick: false, transition: 'scale-transition', nudgeRight: 32, maxWidth: '290px', minWidth: '290px' }
const defaultInternGroupType = 'v-card'

const emitsList = ['update:modelValue', 'update:schema', 'input', 'update', 'resize', 'blur', 'click']

export default function useFormBaseLogic (props, { emit }, slots = {}) {
  const instance = getCurrentInstance()
  const vueInstance = instance?.appContext

  const flatCombinedArray = ref([])

  const formSchema = computed({
    get: () => props.schema ?? {},
    set: (val) => emit('update:schema', val)
  })

  const updateArrayFromState = (data, schema) => {
    flatCombinedArray.value.forEach(obj => {
      obj.value = get(schema, [obj.key, 'type']) === 'select'
        ? get(data, obj.key, null) || get(schema, [obj.key, 'default'], null)
        : get(data, obj.key, null)
      obj.schema = get(schema, obj.key, null)
    })
  }

  const valueIntern = computed(() => {
    const model = props.model ?? props.modelValue
    updateArrayFromState(model, formSchema.value)
    return model
  })

  const parent = computed(() => {
    let p = instance?.proxy
    if (!p) return undefined
    const parentVm = p.$parent
    if (!parentVm?.$parent) return p
    while (p.id?.startsWith?.(p.$parent?.$parent?.id + '-')) {
      p = p.$parent.$parent
    }
    return p
  })

  const index = computed(() => {
    const m = props.id?.match(/\d+/g)
    return m ? m.map(Number) : null
  })

  const getRow = computed(() => props.row || rowDefault)

  const flatCombinedArraySorted = computed(() =>
    orderBy(flatCombinedArray.value, ['schema.sort'], [orderDirection])
  )

  const storeStateData = computed(() => {
    updateArrayFromState(valueIntern.value, formSchema.value)
    return valueIntern.value
  })

  const storeStateSchema = computed(() => {
    updateArrayFromState(valueIntern.value, formSchema.value)
    formEvents.setSchemaInputField(formSchema.value, valueIntern.value)
    return formSchema.value
  })

  const setObjectByPath = (object, path, value) => {
    const pathArray = path.split(pathDelimiter)
    pathArray.forEach((p, ix) => {
      if (ix === pathArray.length - 1) object[p] = value
      object = object[p]
    })
  }

  const sanitizeShorthandType = (key, schema) =>
    isString(schema) ? { type: schema, label: key } : schema

  const flattenObjects = (dat = {}, sch) => {
    const data = {}
    const schema = {}
    Object.keys(sch).forEach(key => {
      sch[key] = sanitizeShorthandType(key, sch[key])
      const bothArray = Array.isArray(dat[key]) && Array.isArray(sch[key])
      const datObjectWithoutSchemaType = isPlainObject(dat[key]) && !sch[key].type
      const datObjectContainsTypeKey = (dat[key]?.type && sch[key]?.type)
      const notInstanceOfFileObject = !(dat[key] instanceof File)

      if (bothArray || datObjectWithoutSchemaType || (datObjectContainsTypeKey && notInstanceOfFileObject)) {
        const { data: flatData, schema: flatSchema } = flattenObjects(dat[key], sch[key])
        Object.keys(flatData).forEach(ii => {
          data[key + pathDelimiter + ii] = flatData[ii]
          schema[key + pathDelimiter + ii] = flatSchema[ii]
        })
      } else {
        data[key] = dat[key]
        schema[key] = sch[key]
      }
    })
    return { data, schema }
  }

  const combineObjectsToArray = ({ data, schema }) => {
    const arr = []
    Object.keys(schema).forEach(key => {
      if (!isPlainObject(schema[key])) return
      arr.push({ key, value: data[key], schema: schema[key] })
    })
    return arr
  }

  const flattenAndCombineToArray = (data, schema) => {
    const flattened = flattenObjects(data, schema)
    return combineObjectsToArray(flattened)
  }

  const autogenerateSchema = (value) => {
    let schema = JSON.stringify(value, (key, val) => val === undefined ? null : val)
    schema = JSON.parse(schema, (key, val) => {
      if (val === null || val === undefined) return defaultSchemaIfValueIsNullOrUndefined(key)
      if (typeof val === 'string') return defaultSchemaIfValueIsString(key)
      if (typeof val === 'number') return defaultSchemaIfValueIsNumber(key)
      if (typeof val === 'boolean') return defaultSchemaIfValueIsBoolean(key)
      return val
    })
    Object.keys(schema).forEach(key => { formSchema.value[key] = schema[key] })
  }

  const tryAutogenerateModelStructure = (model, schema) => {
    Object.keys(schema).forEach(key => {
      if (!isEmpty(model[key])) return
      const val = schema[key]
      if (val.type === 'group') {
        model[key] = {}
        tryAutogenerateModelStructure(model[key], val.schema)
      } else if (val.type === 'array') {
        model[key] = {}
        tryAutogenerateModelStructure(model[key], val.schema)
      } else if (val.type === 'list') {
        model[key] = {}
      } else if (isPlainObject(val) && !val.type) {
        model[key] = {}
        tryAutogenerateModelStructure(model[key], val)
      }
      if (Array.isArray(val) && !val.type) {
        model[key] = {}
        tryAutogenerateModelStructure(model[key], val)
      }
    })
  }

  const rebuildArrays = (model, schema) => {
    if (!model) throw new Error("Property 'model' is null or undefined. Use '<v-form-base :model=\"myModel\" :schema=\"mySchema\" />'. myModel must be at least an empty Object.")
    if (isEmpty(schema) && isEmpty(model)) {
      console.warn("At least one of the properties 'model' or 'schema' in <v-form-base /> must be at least an empty Object. Ignore this Warning on async loading 'model' or 'schema'")
    }
    tryAutogenerateModelStructure(model, schema)
    if (isEmpty(schema) && !props.noAutoGenerateSchema) autogenerateSchema(model)
    flatCombinedArray.value = flattenAndCombineToArray(storeStateData.value, storeStateSchema.value)
  }

  const mapTypeToComponent = (type) =>
    registryMapTypeToComponent(type, vueInstance?.components ?? {})

  const isDateTimeColorTypeAndExtensionText = (obj) =>
    isPicker.includes(obj.schema.type) && obj.schema.ext === 'text'

  const bindOptions = (b) => isString(b) ? { value: b, label: b } : b

  const bindSchemaText = (obj) => ({ ...defaultPickerSchemaText, ...obj.schema.text })
  const bindSchemaMenu = (obj) => ({ ...defaultPickerSchemaMenu, ...obj.schema.menu })

  const bindSchema = (obj) => {
    if (Object.prototype.hasOwnProperty.call(obj.schema, 'falseValue') && obj.schema.falseValue == 0) {
      obj.schema.falseValue = '0'
    }
    return omit(obj.schema, ['type', 'col', 'order', 'offset', 'ext', 'event'])
  }

  const suspendClickAppend = (obj) =>
    /(select|combobox|autocomplete)/.test(obj.schema.type) ? '' : 'click:append'

  const searchInputSync = (obj) =>
    (typeof obj.schema.searchInput !== 'undefined') ? 'search-input' : ''

  const checkExtensionType = (obj) => {
    const ext = ['number', 'range', 'date', 'time', 'color'].includes(obj.schema.ext)
      ? obj.schema.ext
      : null
    return ext || obj.schema.type
  }

  const checkInternType = (obj) => obj.schema.typeInt || obj.schema.type

  const checkInternGroupType = (obj) => {
    const typeInt = obj.schema.typeInt || defaultInternGroupType
    return typeInt.startsWith('v-') || typeInt.startsWith('ue-') ? typeInt : `v-${typeInt}`
  }

  const getKeyForArray = (id, obj, item, idx) => {
    const k = obj.schema.key
    return k ? (Array.isArray(k) ? k.map(i => item[i]).join('_') : item[k]) : (!isNaN(idx) ? `${id}-${obj.key}-${idx}` : idx)
  }

  const getImageSource = (obj) =>
    obj.schema.src ? obj.schema.src : `${obj.schema.base || ''}${obj.value || ''}${obj.schema.tail || ''}`

  const getIconValue = (obj) => obj.schema.label ? obj.schema.label : setValue(obj)

  const getShorthandTooltip = (schemaTooltip) =>
    isString(schemaTooltip) ? { location: 'top', text: schemaTooltip } : schemaTooltip

  const getShorthandTooltipLabel = (schemaTooltip) =>
    isString(schemaTooltip) ? schemaTooltip : schemaTooltip?.label

  const getKeyClassNameWithAppendix = (obj, appendix) =>
    `${appendix ? appendix + classKeyDelimiter : ''}${props.id ? props.id + classKeyDelimiter : ''}${obj.key.replace(/\./g, '-')}`

  const getTypeClassNameWithAppendix = (obj, appendix) =>
    `${appendix ? appendix + classKeyDelimiter : ''}${props.id ? props.id + classKeyDelimiter : ''}${obj.schema.type}`

  const getPropertyClassNameWithAppendix = (obj, appendix) =>
    obj.key ? obj.key.split(pathDelimiter).map(s => `${appendix ? appendix + classKeyDelimiter : ''}${s}`).join(' ') : ''

  const getFormTopSlot = () => `${topSlotAppendix}-${props.id}`
  const getFormBottomSlot = () => `${bottomSlotAppendix}-${props.id}`
  const getKeyInjectSlot = (obj, inject) => getKeyClassNameWithAppendix(obj, `${injectSlotAppendix}-${inject}-${keyClassAppendix}`)
  const getKeyTopSlot = (obj) => getKeyClassNameWithAppendix(obj, `${topSlotAppendix}-${keyClassAppendix}`)
  const getKeyItemSlot = (obj) => getKeyClassNameWithAppendix(obj, `${itemSlotAppendix}-${keyClassAppendix}`)
  const getKeyBottomSlot = (obj) => getKeyClassNameWithAppendix(obj, `${bottomSlotAppendix}-${keyClassAppendix}`)
  const getKeyTooltipSlot = (obj) => getKeyClassNameWithAppendix(obj, `${tooltipSlotAppendix}-${keyClassAppendix}`)
  const getTooltipSlot = () => tooltipSlotAppendix
  const getArrayTopSlot = (obj) => getKeyClassNameWithAppendix(obj, `${topSlotAppendix}-${arrayClassAppendix}`)
  const getArrayItemSlot = (obj) => getKeyClassNameWithAppendix(obj, `${itemSlotAppendix}-${arrayClassAppendix}`)
  const getArrayBottomSlot = (obj) => getKeyClassNameWithAppendix(obj, `${bottomSlotAppendix}-${arrayClassAppendix}`)
  const getTypeTopSlot = (obj) => getTypeClassNameWithAppendix(obj, `${topSlotAppendix}-${typeClassAppendix}`)
  const getTypeItemSlot = (obj) => getTypeClassNameWithAppendix(obj, `${itemSlotAppendix}-${typeClassAppendix}`)
  const getTypeBottomSlot = (obj) => getTypeClassNameWithAppendix(obj, `${bottomSlotAppendix}-${typeClassAppendix}`)

  const getClassName = (obj) =>
    `${itemClassAppendix} ${getTypeClassNameWithAppendix(obj, typeClassAppendix)} ${getKeyClassNameWithAppendix(obj, keyClassAppendix)} ${getPropertyClassNameWithAppendix(obj, propertyClassAppendix)}`

  const gridMapper = (obj, prepender) => {
    if (obj) ['sm', 'md', 'lg', 'xl'].forEach(k => { if (obj[k]) { obj[prepender + k] = obj[k]; delete obj[k] } })
  }
  const gridReplaceXS = (obj, replacer) => { if (obj?.xs) { obj[replacer] = obj.xs; delete obj.xs } }

  const getGridAttributes = (obj) => {
    const colSchema = obj.schema.col || obj.schema.flex
    const colAttr = props.col || props.flex || colDefault
    const colObject = colSchema
      ? (isPlainObject(colSchema) ? colSchema : isNumber(colSchema) || isString(colSchema) ? { cols: colSchema } : { cols: 'auto' })
      : colAttr ? (isPlainObject(colAttr) ? colAttr : isNumber(colAttr) || isString(colAttr) ? { cols: colAttr } : { cols: 'auto' })
        : { cols: 'auto' }
    gridReplaceXS(colObject, 'cols')
    const offset = obj.schema.offset
    const offsetObject = offset ? (isPlainObject(offset) ? { ...offset } : { offset }) : {}
    gridMapper(offsetObject, 'offset-')
    gridReplaceXS(offsetObject, 'offset')
    const order = obj.schema.order
    const orderObject = order ? (isPlainObject(order) ? { ...order } : { order }) : {}
    gridMapper(orderObject, 'order-')
    gridReplaceXS(orderObject, 'order')
    return { ...colObject, ...offsetObject, ...orderObject }
  }

  const getRowGroupOrArray = (obj) => obj.schema.rowGroup || props.rowGroup || rowGroupDefault
  const getColGroupOrArray = (obj) => obj.schema.col || props.col || colDefault

  const getInjectedScopedSlots = (id, obj) => {
    const rx = new RegExp(`${injectSlotAppendix}-(.*?)-${keyClassAppendix}`)
    return Object.keys(slots || {})
      .filter(s => s.includes(`${id}${classKeyDelimiter}${obj.key.replace(/\./g, '-')}`) && s.includes(injectSlotAppendix))
      .map(i => i.match(rx))
      .filter(Boolean)
      .map(m => m[1])
  }

  const toCtrl = (params) => params.obj.schema && isFunction(params.obj.schema.toCtrl) ? params.obj.schema.toCtrl(params) : params.value
  const fromCtrl = (params) => params.obj.schema && isFunction(params.obj.schema.fromCtrl) ? params.obj.schema.fromCtrl(params) : params.value
  const dropCtrl = (params) => params.obj.schema && isFunction(params.obj.schema.drop) ? params.obj.schema.drop(params) : params.value

  const setValue = (obj) =>
    obj.schema.type === 'wrap'
      ? toCtrl({ value: storeStateData.value, obj, data: storeStateData.value, schema: storeStateSchema.value })
      : toCtrl({ value: obj.value, obj, data: storeStateData.value, schema: storeStateSchema.value })

  const setCascadeSelect = (obj) => {
    if (obj.schema.type === 'select' && Object.prototype.hasOwnProperty.call(obj.schema, 'cascade')) {
      const cascadedSelectName = obj.schema.name?.includes?.('repeater') ? obj.schema.name.match(/repeater\d+-input\[\d+\]/) + `[${obj.schema.cascade}]` : obj.schema.cascade
      const cascadeKey = obj.schema.cascadeKey ?? 'items'
      const selectItemValue = obj.schema.itemValue ?? 'id'
      formSchema.value[cascadedSelectName][cascadeKey] = find(obj.schema[cascadeKey], [selectItemValue, valueIntern.value[obj.key]])?.schema ?? []
      const sortIndex = findIndex(flatCombinedArraySorted.value, ['key', cascadedSelectName])
      storeStateData.value[cascadedSelectName] = formSchema.value[cascadedSelectName][cascadeKey].length > 0 ? formSchema.value[cascadedSelectName][cascadeKey][0].value : []
      flatCombinedArraySorted.value[sortIndex].value = valueIntern.value[cascadedSelectName]
      setCascadeSelect(flatCombinedArraySorted.value[sortIndex])
    } else if (obj.schema.type === 'select' && Object.prototype.hasOwnProperty.call(obj.schema, 'autofill')) {
      obj.schema.autofill.forEach(element => {
        if (formSchema.value[element]?.autofillable) {
          storeStateData.value[element] = find(obj.schema.items, ['id', valueIntern.value[obj.key]])?.[element] ?? ''
        }
      })
    }
  }

  const emitValue = (event, val) => {
    const emitEvent = change.includes(event) ? 'onChange' : watchEvents.includes(event) ? 'onWatch' : mouse.includes(event) ? 'onMouse' : display.includes(event) ? 'onDisplay' : event
    if (event === 'onInput' || emitEvent === 'onChange') emit('update:modelValue', storeStateData.value)
    if (emitsList.includes(event)) emit(event, val)
  }

  const onInput = (value, obj, type = 'input') => {
    value = fromCtrl({ value, obj, data: storeStateData.value, schema: storeStateSchema.value })
    value = !value || value === '' ? null : value
    value = obj.schema.type === 'number' ? Number(value) : value
    setObjectByPath(storeStateData.value, obj.key, value)
    obj.value = obj.value !== value ? value : obj.value
    setCascadeSelect(obj)
    formEvents.onInputEventFormData(obj, formSchema.value, storeStateData.value, flatCombinedArraySorted.value, valueIntern.value)
    const emitObj = { on: type, id: props.id, index: index.value, params: { index: index.value, lastValue: obj.value }, key: obj.key, value, obj, data: storeStateData.value, schema: storeStateSchema.value, parent: parent.value }
    emitValue(type, emitObj)
    return emitObj
  }

  const onInputWrapper = (emitObj, wrapperObj) => {
    emitValue('input', emitObj)
  }

  const onEvent = (event = {}, obj, tag) => {
    const emitObj = {
      on: event.type,
      id: props.id,
      index: index.value,
      params: { text: event?.srcElement?.innerText, tag, model: obj.schema.model, open: obj.schema.open, index: index.value },
      key: obj.key,
      value: obj.value,
      obj,
      event,
      data: storeStateData.value,
      schema: storeStateSchema.value,
      parent: event.type !== 'dragstart' ? parent.value : undefined
    }
    delay(() => emitValue(event.type, emitObj), onEventDelay)
    return emitObj
  }

  const onClickOutside = (event, obj) => {
    if (!obj.schema?.clickOutside) return
    if (isFunction(obj.schema.clickOutside)) return obj.schema.clickOutside(obj, event)
    emitValue('clickOutside', { on: 'clickOutside', id: props.id, key: obj.key, value: obj.value, obj, params: { x: event.clientX, y: event.clientY }, event, data: storeStateData.value, schema: storeStateSchema.value })
  }

  const onIntersect = (isIntersecting, entries, observer, obj) => {
    emitValue('intersect', { on: 'intersect', id: props.id, index: index.value, key: obj.key, value: obj.value, obj, params: { isIntersecting, entries, observer }, data: storeStateData.value, schema: storeStateSchema.value })
  }

  const onSwipe = (tag, obj) => {
    emitValue('swipe', { on: 'swipe', id: props.id, key: obj.key, value: obj.value, obj, params: { tag }, data: storeStateData.value, schema: storeStateSchema.value })
  }

  const onResize = (event) => {
    emitValue('resize', { on: 'resize', id: props.id, params: { x: window.innerWidth, y: window.innerHeight }, event, data: storeStateData.value, schema: storeStateSchema.value })
  }

  const dragstart = (event, obj) => {
    if (!obj.schema.drag) return
    event.dataTransfer.dropEffect = dropEffect
    event.dataTransfer.effectAllowed = dropEffect
    event.dataTransfer.setData('text', JSON.stringify(onEvent(event, obj)))
  }

  const dragover = (event, obj) => obj.schema.drop ? event.preventDefault() : null

  const drop = (event, obj) => {
    if (!obj.schema.drop) return event.preventDefault()
    obj.dragEvent = JSON.parse(event.dataTransfer.getData('text'))
    if (obj.key === obj.dragEvent.obj.key && props.id === obj.dragEvent.id) return event.preventDefault()
    if (isFunction(obj.schema.drop)) obj.value = dropCtrl({ value: obj.dragEvent.value, obj, event, data: storeStateData.value, schema: storeStateSchema.value })
    onEvent(event, obj)
    event.preventDefault()
  }

  const updateInput = (event, obj) => {
    if (Array.isArray(event)) {
      const oldFormSchema = cloneDeep(formSchema.value)
      let isChanged = false
      event.forEach(e => {
        if (e.key && (e.value !== null || e.value !== undefined) && formSchema.value?.[obj.key]) {
          oldFormSchema[obj.key][e.key] = e.value
          isChanged = true
        }
      })
      if (isChanged) formSchema.value = oldFormSchema
    } else if (event.key && event.value && formSchema.value?.[obj.key]) {
      const oldFormSchema = cloneDeep(formSchema.value)
      oldFormSchema[obj.key][event.key] = event.value
      formSchema.value = oldFormSchema
    }
  }

  return {
    id: computed(() => props.id ?? defaultID),
    flatCombinedArray,
    flatCombinedArraySorted,
    valueIntern,
    formSchema,
    storeStateData,
    storeStateSchema,
    parent,
    index,
    getRow,
    vueInstance,
    mapTypeToComponent,
    isDateTimeColorTypeAndExtensionText,
    bindOptions,
    bindSchemaText,
    bindSchemaMenu,
    bindSchema,
    suspendClickAppend,
    searchInputSync,
    checkExtensionType,
    checkInternType,
    checkInternGroupType,
    getKeyForArray,
    getImageSource,
    getIconValue,
    getShorthandTooltip,
    getShorthandTooltipLabel,
    getFormTopSlot,
    getFormBottomSlot,
    getKeyInjectSlot,
    getKeyTopSlot,
    getKeyItemSlot,
    getKeyBottomSlot,
    getKeyTooltipSlot,
    getTooltipSlot,
    getArrayTopSlot,
    getArrayItemSlot,
    getArrayBottomSlot,
    getTypeTopSlot,
    getTypeItemSlot,
    getTypeBottomSlot,
    getClassName,
    getGridAttributes,
    getRowGroupOrArray,
    getColGroupOrArray,
    getInjectedScopedSlots,
    setValue,
    onInput,
    onInputWrapper,
    onEvent,
    onClickOutside,
    onIntersect,
    onSwipe,
    onResize,
    dragstart,
    dragover,
    drop,
    updateInput,
    rebuildArrays
  }
}
