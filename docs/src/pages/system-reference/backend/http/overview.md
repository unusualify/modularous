---
sidebarPos: 1
sidebarTitle: Overview
---

# HTTP Layer

**Directory**: `src/Http/`

This section mirrors the `src/Http/` namespace, which contains the HTTP-facing pieces of Modularous: middleware, request classes, controllers (documented separately under [Controllers](/system-reference/backend/http/controllers/overview)), and view composers (documented separately under [View Composers](/system-reference/backend/http/view-composers/overview)).

## Groups

| Group | Summary | Page |
|-------|---------|------|
| **Controllers** | Core HTTP controller hierarchy (`CoreController` → `PanelController` → `BaseController`) plus feature controllers for auth, media, files, profile, process, and API flows | [Controllers →](/system-reference/backend/http/controllers/overview) |
| **Middleware** | 14 `modularous.*` middleware aliases and 4 groups registered during route bootstrapping | [Middleware →](/system-reference/backend/http/middleware/overview) |
| **Requests** | 7 `FormRequest` classes — two base classes (`BaseFormRequest`, `Request`) plus five concrete requests for file/media uploads, OAuth, and role/permission creation | [Requests →](/system-reference/backend/http/request/overview) |
| **View Composers** | Shared view-binding classes (`ActiveNavigation`, `CurrentUser`, uploader configs, localization, and URLs) that inject HTTP-layer data into views | [View Composers →](/system-reference/backend/http/view-composers/overview) |
