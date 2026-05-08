---
sidebarPos: 1
sidebarTitle: Overview
sidebarGroupTitle: View Composers
---

# View Composers

**Directory**: `src/Http/ViewComposers/`

View composers are classes that bind data to every view (or Inertia response) before it is rendered. Modularous registers them automatically in `BaseServiceProvider`. They inject cross-cutting data — the authenticated user, active navigation state, upload configuration, locale settings, and common URLs — so controllers never have to pass this data manually.

## Composers

| Composer | Variable injected | Description |
|----------|-------------------|-------------|
| [ActiveNavigation](./active-navigation) | `_global_active_navigation`, `_primary_active_navigation`, `_secondary_active_navigation` | Parses the current route name into navigation depth markers |
| [CurrentUser](./current-user) | `currentUser` | Injects the authenticated user profile using Modularous auth guard |
| [FilesUploaderConfig](./files-uploader-config) | `filesUploaderConfig` | Builds the file-library upload configuration object (endpoint, CSRF, ACL, limits) |
| [Localization](./localization) | `modularityLocalization` | Injects the full locale/language configuration for frontend i18n |
| [MediasUploaderConfig](./medias-uploader-config) | `mediasUploaderConfig` | Builds the media-library upload configuration object (endpoint, CSRF, ACL, limits) |
| [Urls](./urls) | `urls` | Injects commonly used admin route URLs (profile show/update) |

## Registration

All composers are bound in `BaseServiceProvider::registerViewComposers()`. They run on every request that renders a view, so the injected variables are always available in Blade templates and Inertia shared props without any controller boilerplate.
