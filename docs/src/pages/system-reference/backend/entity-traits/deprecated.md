---
sidebarPos: 4
sidebarTitle: Deprecated Aliases
---

# Deprecated Trait Aliases

Two top-level traits exist only for backwards compatibility. Both delegate entirely to their `Core\` counterparts. Do not use them in new code.

---

## HasScopes *(deprecated)*

**Namespace**: `Unusualify\Modularity\Entities\Traits\HasScopes`  
**Delegates to**: `Unusualify\Modularity\Entities\Traits\Core\HasScopes`

::: warning Deprecated
Use `Core\HasScopes` directly. This alias will be removed in a future major version.
:::

### Migration

```php
// Before (deprecated)
use Unusualify\Modularity\Entities\Traits\HasScopes;

// After
use Unusualify\Modularity\Entities\Traits\Core\HasScopes;
```

All methods and scopes are identical. See [Core Traits →](./core/overview) for the full reference.

---

## ModelHelpers *(deprecated)*

**Namespace**: `Unusualify\Modularity\Entities\Traits\ModelHelpers`  
**Delegates to**: `Unusualify\Modularity\Entities\Traits\Core\ModelHelpers`

::: warning Deprecated
Use `Core\ModelHelpers` directly. This alias will be removed in a future major version.
:::

### Migration

```php
// Before (deprecated)
use Unusualify\Modularity\Entities\Traits\ModelHelpers;

// After
use Unusualify\Modularity\Entities\Traits\Core\ModelHelpers;
```

All methods and boot behavior are identical. See [Core Traits →](./core/overview) for the full reference.
