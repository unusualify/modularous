// hooks/utils/useBadge.js
import { computed } from 'vue'
import { toNumber, isNumber } from 'lodash'
import { isset, isString } from '@/utils/helpers'

export default function useBadge(props, context) {

  const isBadge = (action) => {
    if(!isset(action.badge)){
      return false
    }

    let badge = action.badge

    if(isString(badge)){
      let badgeNumber = toNumber(badge)

      if(!isNaN(badgeNumber))
        return badgeNumber > 0
    }

    return badge
  }

  const badgeProps = (action) => {
    return {
      ...(action.componentProps ?? {}),
      content: action.badgeContent ?? action.badge,
      color: action.badgeColor ?? 'warning',
      textColor: action.badgeTextColor ?? 'white',
    }
  }

  return {
    isBadge,
    badgeProps
  }
}
