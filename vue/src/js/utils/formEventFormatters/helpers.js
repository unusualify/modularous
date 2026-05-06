// utils/formEventFormatters/helpers.js

export default {
  handlers: (input, model, index = null) => {
    const handlerName = input.name
    const handlerModelName = input.name
    const handlerSchemaName = input.key ?? input.name
    const handlerSchema = input // schema[index][handlerName]
    let handlerValue = __data_get(model, !isNaN(parseInt(index)) ? `${index}.${handlerModelName}` : handlerModelName)

    if(!handlerValue && __isset(handlerSchema.parentName)){
      handlerValue = __data_get(model, !isNaN(index) ? `${index}.${handlerSchema.parentName}.${handlerModelName}` : `${handlerSchema.parentName}.${handlerModelName}`)
    }

    return {
      handlerName,
      handlerModelName,
      handlerSchemaName,
      handlerSchema,
      handlerValue
    }
  },

  getInputToFormat: (args, model, schema, input, index) => {

    let inputToFormat = args.shift() // 2.packages || package
    let inputNotationParts = []

    let stages = inputToFormat.split('.')
    let targetFormIndex = parseInt(stages[0])

    if(isNaN(targetFormIndex)){
      targetFormIndex = index
    }else if(!Array.isArray(model)){
      return false
    }

    if(Array.isArray(model)){
      if(!isNaN(targetFormIndex)){
        targetFormIndex -= 1
        stages.shift()
      }
      inputNotationParts.push(`[${targetFormIndex}]`)
    }

    inputToFormat = stages.join('.')
    inputNotationParts.push(inputToFormat)

    return inputNotationParts.join('.')
  },

  hydrateModelNotation: (modelNotation, model, schema, input, index) => {
    let modelNotationParts = []
    let stages = modelNotation.split('.')
    let targetFormIndex = parseInt(stages[0])

    if(isNaN(targetFormIndex)){
      targetFormIndex = index
    } else if(!Array.isArray(model)){
      return false
    }

    if(Array.isArray(model)){
      if(!isNaN(targetFormIndex)){
        targetFormIndex -= 1
        stages.shift()
      }
      modelNotationParts.push(`[${targetFormIndex}]`)
    }

    modelNotationParts.push(stages.join('.'))

    return modelNotationParts.join('.')
  },

  getNewValue: (setPropFormat, handlerValue, handlerSchema) => {
    let newValue

    if(handlerValue){
      if((handlerSchema.accordingToEmptiness ?? false) && ['string', 'number', 'array', 'object'].includes(typeof handlerValue)){
        if( (typeof handlerValue === 'string' && handlerValue.trim() !== '' )
          || (typeof handlerValue === 'number' && handlerValue > 0 )
          || (Array.isArray(handlerValue) && handlerValue.length > 0 )
          || (typeof handlerValue === 'object' && Object.keys(handlerValue).length > 0)){
          handlerValue = 1
        }else{
          handlerValue = 0
        }
      }

      let dataSet = []
      let notation = __wildcard_change(setPropFormat, handlerValue)

      if(notation.match(/^(modelValue|model)$/g)){
        dataSet = handlerValue
      } else {
        dataSet = __data_get(handlerSchema, notation, null)
      }

      if(Array.isArray(dataSet) && (dataSet.length > 0)){
        newValue = dataSet.shift()

      }else if(dataSet !== undefined && dataSet !== null){
        newValue = dataSet
      }
    }

    return newValue
  },
}
