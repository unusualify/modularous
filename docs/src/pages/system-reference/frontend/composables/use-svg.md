---
sidebarTitle: useSvg
---

# useSvg

Provides utilities for checking SVG symbol availability and resolving locale-aware SVG symbol names.

**File:** `vue/src/js/hooks/useSvg.js`

---

## Usage

```js
import { useSvg } from '@/hooks'

const { symbolExists, isHotSvg, getLocaleSymbol } = useSvg()

if (symbolExists('icon-home')) {
  // render SVG symbol
}

// Get a locale-specific variant, falling back to a default
const symbol = getLocaleSymbol('flag', 'flag-default')
```

## Returns

| Name | Signature | Description |
|------|-----------|-------------|
| `symbolExists` | `(symbol: String) => Boolean` | Returns `true` if the given SVG symbol ID exists in the page's SVG sprite |
| `isHotSvg` | `() => Boolean` | Returns `true` when SVG hot-reload mode is active (development only) |
| `getLocaleSymbol` | `(symbol: String, fallback: String) => String` | Returns a locale-specific symbol name, falling back to `fallback` if the locale variant doesn't exist |

## Notes

- The underlying utilities are in `vue/src/js/utils/svg.js`.
- `symbolExists` is useful for conditionally rendering an SVG icon vs. a Vuetify MDI fallback.
- `getLocaleSymbol` is primarily used for flag icons or other locale-sensitive imagery.
