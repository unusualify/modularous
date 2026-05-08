---
sidebarPos: 6
sidebarTitle: Model Behavior
sidebarGroupTitle: Model Behavior
---

# Model Behavior Traits

These traits modify how individual model instances behave: slug routing, UUID keys, position ordering, dynamic spread attributes, state machines, and presenter patterns.

| Trait | Description |
|-------|-------------|
| [HasSlug](./has-slug) | Slug generation, locale-aware slug storage, and `resolveRouteBinding` override |
| [HasUuid](./has-uuid) | Ordered UUID primary key replacing auto-increment |
| [HasPosition](./has-position) | Integer `position` column with auto-assignment and drag-and-drop reorder |
| [HasSpreadable](./has-spreadable) | Arbitrary JSON attributes stored in a `Spread` morph record, surfaced as model properties |
| [HasStateable](./has-stateable) | State machine backed by `Stateable` morph and shared `states` table |
| [HasPresenter](./has-presenter) | Lightweight presenter pattern wrapping the model in a display class |
