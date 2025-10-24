// hooks/useSvg.js
import { ref } from 'vue';
import { svgSymbolExists, isHotSvgMode, getLocaleSymbol as getLocaleSymbolUtils } from '@/utils/svg';
export default function useSvg() {

  const symbolExists = (symbol) => {
    return svgSymbolExists(symbol)
  }

  const isHotSvg = () => {
    return isHotSvgMode()
  }

  const getLocaleSymbol = (symbol, fallback) => {
    return getLocaleSymbolUtils(symbol, fallback)
  }

  return {
    symbolExists,
    isHotSvg,
    getLocaleSymbol
  }
}
