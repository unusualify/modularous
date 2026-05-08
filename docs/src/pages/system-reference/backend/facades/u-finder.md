---
sidebarPos: 19
sidebarTitle: UFinder (deprecated)
---

# UFinder *(deprecated)*

**Facade**: `Unusualify\Modularity\Facades\UFinder`  
**Accessor**: `Unusualify\Modularity\Support\Finder::class`  
**Underlying**: `Unusualify\Modularity\Support\Finder`

::: warning Deprecated
`UFinder` is deprecated. Use [`ModularityFinder`](./modularity-finder) instead. Both facades resolve to the same underlying `Finder` class — `UFinder` exists only for backwards compatibility.
:::

## Migration

Replace all usages of `UFinder` with `ModularityFinder`:

```php
// Before (deprecated)
use Unusualify\Modularity\Facades\UFinder;
$model = UFinder::getModel('blog_posts');

// After
use Unusualify\Modularity\Facades\ModularityFinder;
$model = ModularityFinder::getModel('blog_posts');
```

All methods are identical. See [ModularityFinder](./modularity-finder) for the full method reference.
