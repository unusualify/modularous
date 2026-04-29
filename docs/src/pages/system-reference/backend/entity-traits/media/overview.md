---
sidebarPos: 5
sidebarTitle: Media Traits
sidebarGroupTitle: Media Traits
---

# Media Traits

Three traits handle file and image attachments. They all build on morph pivot tables so any model can attach media without schema changes.

| Trait | Description |
|-------|-------------|
| [HasImages](./has-images) | Media library images via `MorphToMany` with crop, LQIP, and social URL helpers |
| [HasFiles](./has-files) | File attachments via `MorphToMany` with locale-aware retrieval |
| [HasFileponds](./has-fileponds) | Filepond temp-file tracking with collection change management |
