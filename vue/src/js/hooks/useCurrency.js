import { computed, reactive, toRefs } from 'vue'
import { useI18n } from 'vue-i18n'
import { formatCurrencyPrice } from '@/utils/helpers'

// export const useCurrencyProps = {
//   amount: {
//     type: [Number],
//     // default: 0
//   },
//   symbol: {
//     type: String,
//     // default: '$'
//   }
// }

export default function useCurrency(props) {

  const { locale } = useI18n({ useScope: 'global' })


  const methods = {
    formatPrice: (amount, symbol) => {
      return formatCurrencyPrice(amount, symbol, locale.value ?? locale)
    }
  }

  return {
    ...methods
  }
}
