---
sidebarPos: 3
sidebarTitle: Core Traits
sidebarGroupTitle: Core Traits
---

# Core Traits

The `Core/` namespace contains the low-level plumbing traits that power scopes, caching, change tracking, locale-aware tagging, and shared model helpers. Most top-level traits depend on one or more of these.

| Trait | Description |
|-------|-------------|
| [ModelHelpers](./model-helpers) | Master composition trait: scopes, routes, activity logging, title helpers |
| [HasScopes](./has-scopes) | Standard visibility scopes and global scope registration convention |
| [HasCaching](./has-caching) | Automatic cache invalidation via `CacheObserver` |
| [HasCacheDependents](./has-cache-dependents) | Cross-model cache dependency graph |
| [HasCompany](./has-company) | Company association with auto-create on save |
| [ChangeRelationships](./change-relationships) | Tracks which relationships changed during a request cycle |
| [LocaleTags](./locale-tags) | Locale-scoped tagging via the `tagged` pivot table |
