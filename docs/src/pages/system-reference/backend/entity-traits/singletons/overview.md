---
sidebarPos: 12
sidebarTitle: Singleton Traits
sidebarGroupTitle: Singleton Traits
---

# Singleton Traits

These traits handle models that exist as a single instance (site settings, homepage content) or that need hostable/subdomain-aware slug routing across a parent hierarchy.

| Trait | Description |
|-------|-------------|
| [IsSingular](./is-singular) | Stores all fillable fields as JSON in a shared `modularous_singletons` table |
| [IsHostable](./is-hostable) | Multi-level slug routing across a `BelongsTo`/`HasMany` hierarchy |
