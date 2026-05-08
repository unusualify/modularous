---
sidebarTitle: useModule
---

# useModule

Derives i18n-translated module names, permission key, and layout metadata from a component's `name` / `moduleName` / `routeName` props. Used by table and page components to display localised module titles without hardcoding strings.

**File:** `vue/src/js/hooks/useModule.js`  
**Props factory:** `makeModuleProps`

---

## Usage

```js
import { useModule, makeModuleProps } from '@/hooks'

const props = defineProps({ ...makeModuleProps() })
const {
  transNameSingular,
  transNamePlural,
  permissionName,
  snakeName,
  searchPlaceholder
} = useModule(props, context)
```

```html
<h1>{{ transNamePlural }}</h1>
<p>{{ searchPlaceholder }}</p>
```

## Props (via `makeModuleProps`)

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `name` | `String` | — | Module or route name (used for i18n and permission key) |
| `customTitle` | `String` | — | Override the translated plural name |
| `titlePrefix` | `String` | `''` | Prepend a string to the title |
| `titleKey` | `String` | `'name'` | Key used for the display title |
| `fillHeight` | `Boolean` | `false` | Expand component to fill container height |
| `slots` | `Object` | `{}` | Slot overrides |
| `noFullScreen` | `Boolean` | `false` | Disable fullscreen toggle |
| `endpoints` | `Object` | `{}` | Endpoint URL map |
| `items` | `Array` | `[]` | Initial item list |

## Returns

| Name | Type | Description |
|------|------|-------------|
| `snakeName` | `String` | `_.snakeCase(props.name)` |
| `moduleSnakeName` | `String` | `_.snakeCase(props.moduleName ?? props.name)` |
| `routeSnakeName` | `String` | `_.snakeCase(props.routeName ?? props.name)` |
| `tableTranslationNotation` | `String` | Full dot-notation i18n key, e.g. `modules.blog.posts.name` |
| `transNameSingular` | `ComputedRef<String>` | `t(notation, 0)` — singular form |
| `transNamePlural` | `ComputedRef<String>` | `t(notation, 1)` — plural form |
| `permissionName` | `ComputedRef<String>` | `_.kebabCase(name)` — used for permission checks |
| `searchPlaceholder` | `String` | `t('Type to Search')` |
| `searchModel` | `String` | Initial empty search string |
| `elements` | `Array` | Initial items from `props.items` |
| `windowSize` | `Object` | `{ x, y }` — updated by `onResize` |
| `onResize` | `Function` | Updates `windowSize` from `window.innerWidth/Height` |

## Translation key structure

The hook resolves i18n keys from `modules.*`:

```
// For a module named 'Blog' with route 'Post':
tableTranslationNotation = 'modules.blog.post.name'

// In your lang file:
{
  modules: {
    blog: {
      post: { name: 'Post | Posts' }
    }
  }
}
```

## See Also

- [useAuthorization](/system-reference/frontend/composables/use-authorization) — uses `permissionName` to scope permission checks
