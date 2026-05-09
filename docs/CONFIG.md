# Modularous Configuration System

Modularous uses a layered configuration system. Understanding the layers helps when customizing or debugging.

## Configuration Layers

### 1. merges (Package Defaults)

**Location**: `config/merges/*.php`  
**Loaded**: At bootstrap (BaseServiceProvider::registerBaseConfigs)  
**Key**: `modularous.{filename}` (e.g. `modularous.services`, `modularous.roles`)

Package defaults that do not depend on the translator. Merged recursively with `array_merge_recursive_preserve()`.

**Examples**:
- `merges/services.php` → `config('modularous.services')`
- `merges/roles.php` → `config('modularous.roles')`
- `merges/traits.php` → `config('modularous.traits')`

### 2. defers (Localized Config)

**Location**: `config/defers/*.php`  
**Loaded**: Per request via `LoadLocalizedConfig` middleware (runs in `modularous.core` group)  
**Key**: `modularous.{filename}`

Config that needs the translator (e.g. `__()`, `___()`). Loaded after the translator is available so translation keys resolve correctly.

**Examples**:
- `defers/navigation.php` → sidebar labels, menu items
- `defers/form_drafts.php` → form field labels
- `defers/widgets.php` → widget titles

### 3. publishes (App Overrides)

**Location**: Published to `config/` via `php artisan vendor:publish --tag=modularous-config`  
**Loaded**: Standard Laravel config loading

App-level overrides. Published files take precedence when merged.

**Common published configs**:
- `config/modularous.php` (base config)
- `config/modules.php` (module paths, namespaces)
- `config/permission.php`
- `config/auth.php` (modularous guard)

### 4. App Override Path

**Location**: `base_path('modularous/*.php')`  
**Loaded**: By `LoadLocalizedConfig` middleware when files exist

Optional app-specific config files that override deferred config. Check `LoadLocalizedConfig` for the exact merge order.

## Base Config

**File**: `config/config.php`  
**Key**: `modularous` (via `$baseKey`)

Core package settings: app_url, admin paths, theme, enabled features, etc.

## CMS revisions

CMS **Pages** use the core revision stack: `HasRevisions` on the model, `PageRevision` rows (`tables.cms_pages_revisions` / `um_cms_pages_revisions`), and repository methods from `RevisionsTrait` (list, restore, approve/reject, preview). There is no separate “revision snapshot” config or UUID snapshot table.

## CMS signed public preview

**Config**: `modularous.cms_routing.signed_preview` (`config/merges/cms_routing.php`)

- `enabled` — `MODULAROUS_CMS_SIGNED_PREVIEW_ENABLED` (default true).
- `path_prefix` — URL prefix for the **public** signed route (default `cms/preview`), e.g. `GET /cms/preview/{Module}/{Route}/{id}/{locale?}` (Studly module + submodule, numeric id) with `signature` + `expires` query parameters.
- `ttl_minutes` — `MODULAROUS_CMS_SIGNED_PREVIEW_TTL_MINUTES` (minimum 5).
- `throttle_max_attempts` / `throttle_decay_minutes` — per-IP throttle on the signed preview route.

**Behavior:** `CmsSignedPublicPreviewController` picks the correct `Front\*Controller` subclass for the given module + route, then renders the same Blade as the public catch-all but loads the row **without** `published` / `visible` scopes. Applies to any submodule whose model uses `HasParentSegment` and has a `CmsController` front handler. Response uses `noindex, nofollow`. Visitor redirect middleware skips paths under `path_prefix` so redirect rules do not intercept preview URLs.

**Panel:** Authenticated `GET …/cms/signed-public-preview/{module}/{route}/{id}?locale=` (Cms module web group; route name `*.signed_public_preview.mint`) returns JSON `{ url, expires_in_minutes }`. The edit form shows **Copy shareable preview link** when `signedPublicPreview` is present (any qualifying submodule, not only Page).

## CMS publish window schedule (optional)

Visitor-visible pages already respect `publish_start_date` / `publish_end_date` at **query time** via `published` + `visible` scopes; no cron is required for 404/unpublish behavior.

**Config**: `modularous.cms_schedule` (`config/merges/cms_schedule.php`)

- `enabled` — master toggle for the scan job’s internal work.
- `register_with_laravel_schedule` — `MODULAROUS_CMS_SCHEDULE_REGISTER`: when **true**, `CmsServiceProvider` registers `ScanCmsPublishWindowBoundariesJob` on Laravel’s scheduler (default **false** so hosts opt in explicitly).
- `frequency` — `everyMinute`, `everyFiveMinutes` (default), or `hourly`.
- `boundary_window_minutes` — look-back window; rows whose `publish_start_date` or `publish_end_date` falls in `(now - window, now]` trigger an event.
- `log_events` — log each boundary to the default log channel.
- `cache_flush_tags` — optional comma-separated list (env `MODULAROUS_CMS_SCHEDULE_CACHE_TAGS`); when the store supports tagging, tags are flushed once per scan if any boundary fired.

**Event:** `CmsPublishWindowBoundaryReached` (`modelClass`, `modelId`, `publish_start` | `publish_end`). Listen in the host app for notifications, CDN purge, or search indexing.

**Models:** `modularous.cms_schedule.publish_window_models` — list of Eloquent FQCNs (default `Page` only). Comma-separated env `MODULAROUS_CMS_PUBLISH_WINDOW_MODELS` overrides. Each table is skipped unless it exists; only columns that exist (`publish_start_date`, `publish_end_date`) are queried.

**Host registration (manual):** If you prefer not to use `register_with_laravel_schedule`, register the job from the application’s `routes/console.php` (Laravel 11+) or `App\Console\Kernel`:

```php
use Illuminate\Support\Facades\Schedule;
use Modules\Cms\Jobs\ScanCmsPublishWindowBoundariesJob;

Schedule::job(new ScanCmsPublishWindowBoundariesJob)->everyFiveMinutes();
```

Ensure the scheduler is running (`php artisan schedule:work` in development, cron `schedule:run` in production).

## Currency Provider

**Config**: `modularous.currency_provider`  
**Env**: `MODULAROUS_CURRENCY_PROVIDER`

Optional FQCN of a class implementing `CurrencyProviderInterface`. When null, Modularous uses `SystemPricingCurrencyProvider` if the SystemPricing module is present, else `NullCurrencyProvider`.

## Paths

**Config**: `modularous.paths` (from merges/paths.php)

Defines base paths for modules, vendor assets, and published resources.

## Security Step-Up (Capability Whitelist)

**Config**: `modularous.security.step_up` (only `enabled` toggle)

Step-up now has an explicit whitelist:

- `enabled` (bool): global toggle.
- `required_capabilities` is no longer managed from config.

Default:

```php
'step_up' => [
    'enabled' => false,
    'required_capabilities' => [], // runtime-managed
],
```

Behavior:

- `required_capabilities` is built dynamically from `SystemUser > Capability` records where `requires_step_up = true`.
- If route uses `modularous.security.step_up:<capability>`, that `<capability>` must be active and marked as `requires_step_up`.
- User must also own that capability via Capability record role assignments.

How to fill (UI):

1. Open `System > User Management > Capabilities`.
2. Create capability keys (examples: `promotion.execute`, `scripts.manage`, `redirect.manage`).
3. Assign allowed roles for each capability (stored via `um_role_capability` pivot).
4. Toggle `Require Step-Up` for capabilities that need re-auth.
5. (Optional) Enable `Strict Route Binding` and bind route names under `Capability Routes`.
6. Keep `published` active for capabilities that should be effective.

Route usage:

- Use middleware with capability argument, e.g. `modularous.security.step_up:promotion.execute`.

Route binding model:

- Capability routes are stored in `um_capability_routes`.
- If `strict_route_binding = false`, capability match alone is enough.
- If `strict_route_binding = true`, current Laravel route name must be in active bound routes for that capability.

Route discovery endpoint:

- `GET {admin-prefix}/system/system-user/capabilities/discover-routes` (web-auth protected)
- Query params:
  - `search` or `q`: filter by route name/uri
  - `only_named` (default: `true`)
  - `itemsPerPage` or `limit`
  - `page`

Suggested async input binding:

```php
[
    'type' => 'select-scroll',
    'componentType' => 'v-autocomplete',
    'name' => 'route_name',
    'label' => 'Route Name',
    'endpoint' => 'admin.system.system_user.capabilities.discover_routes',
    'itemTitle' => 'name_with_uri',
    'itemValue' => 'name',
    'itemsPerPage' => 100,
    'page' => 1,
    'searchKeys' => ['name', 'uri'],
]
```

`capability_route` stores only the Laravel route name. `uri` and `method` stay discoverable metadata and are not persisted.

Capabilities bind to routes through a pivot table. In practice:

- Manage the route registry from `CapabilityRoute`
- Assign one or many registered routes from the `Capability` form via the `routes` field

## CMS public URL + robots.txt

**Routing (`modularous.cms_routing`)** — `config/merges/cms_routing.php`: `front_route_prefix` (public CMS path segment, default `cms`), `default_locale`, `canonical_host`, `redirect_to_canonical`, visitor redirect toggles. Front catch-all: `modules/Cms/Routes/front.php` → `PublicPageController`. **Admin:** `admin.slug_nested_path_warnings`, `admin.slug_public_path_preview` (slug alanında segment uyarıları + canlı path ipucu; hydrate `cmsPublicPathPreview`); `admin.slug_max_path_segments` (`MODULAROUS_CMS_ADMIN_SLUG_MAX_PATH_SEGMENTS`, default sınırsız) — üst sınırı aşan çok segmentli slug girişi `CmsSlugInputValidationService` ile reddedilir (ör. `1` = tek segment). **API:** authenticated `GET …/cms/routing-meta` (Modularous API prefix + `cms/routing-meta`; `CmsRoutingMetaController`) — prefix + locale defaults + `admin.slug_*` özeti. **Panel `Slug.vue`:** slug doğrulama POST’u `useStepUpAwareJsonPost` (HTTP 428 + step-up); aynı meta URL’den istemci tarafı segment limiti kuralı (backend ile uyumlu).

**SEO (`modularous.cms_seo`)** — `config/merges/cms_seo.php`: `canonical.*` (lowercase path, trailing slash); `robots.route_enabled` (`MODULAROUS_CMS_ROBOTS_TXT_ROUTE_ENABLED`) registers **GET `/robots.txt`** (`cms.robots_txt`) with `web` middleware. **Body resolution:** when `robots.use_site_settings` is true (default, `MODULAROUS_CMS_SEO_ROBOTS_USE_SITE_SETTINGS`), the first source is the `um_cms_site_settings` row keyed by `robots.site_setting` (default group `seo`, key `global_robots_txt`, locale `*`); otherwise (or when disabled) use `robots.global_robots_txt` (`MODULAROUS_CMS_SEO_GLOBAL_ROBOTS_TXT`). Empty/whitespace falls back to `User-agent: *\nAllow: /`. **Panel:** Inertia **Site SEO** (`admin.system.cms.siteSeo.*`) edits the DB-backed body. **`admin.publish_soft_warnings`** — publish sonrası SEO boş alan uyarıları (`CmsAdminWarnings`). **`admin.publish_schedule_warnings`** (`MODULAROUS_CMS_ADMIN_PUBLISH_SCHEDULE_WARNINGS`) — yayınlanmış sayfa kaydedildiğinde, isteğe bağlı `publish_start_date` / `publish_end_date` penceresi dışındaysa (ziyaretçi tarafında zaten `HasScopes::scopeVisible` ile görünmez) panelde yumuşak uyarı. Disable the robots route if the host app serves its own `robots.txt`.

**Site-wide keys:** Generic key-value rows remain available under the CMS `SiteSetting` CRUD (`site_setting` route); the Site SEO screen targets the global robots body specifically.

**Sitemap (`modularous.cms_sitemap`)** — `config/merges/cms_sitemap.php`: `route_enabled` registers **GET `/sitemap.xml`** (`cms.sitemap`, `web` middleware) from the committed cache (`CmsSitemapCacheService`); `cache_key`; optional `build_on_cache_miss` (off by default); `default_sitemap_id` (matches seeded row in `um_cms_sitemaps`); `defaults.changefreq` / `defaults.priority` when a row in `um_cms_sitemapables` has no override; `lastmod.source` = base model `updated_at` (see `CmsSitemapBuildService` PHPDoc). **Panel:** Inertia **`Sitemap`** (`vue/src/js/Pages/Sitemap.vue`) — `GET …/sitemap` (`sitemap.tool`), `POST …/sitemap/dry-run` (`sitemap.dryRun.web`), `POST …/sitemap/commit` (`sitemap.commit.web`); `SitemapRequest` + `SitemapRepository` (`getPanelDryRunPayload` / `commitSitemapToLiveCache`); `SitemapController` (shell) like other submodule controllers; when step-up is enabled, **commit** uses `panel.step_up_ability.commit` (default `sitemap.commit`). Sidebar: `config/defers/navigation.php` → `_cms_sitemap` (`admin.system.cms.sitemap.tool`). **Module config** (`modules/Cms/Config/config.php` → `sitemap`) documents the tool. **Build:** `CmsSitemapBuildService` + **commit:** `php artisan cms:sitemap:rebuild` or `Modules\Cms\Jobs\RebuildCmsSitemapJob`.

**Parent segment (modül ortak URL öneki):** `modularous.cms_parent_segments` (`config/merges/cms_parent_segments.php`, `MODULAROUS_CMS_PARENT_SEGMENTS_ENABLED`). Tablolar: `tables.cms_parent_segments`, `tables.cms_parent_segment_targets` — katalog + `target_class` (FQCN, `urlable_id` yok) + `locale` (boş = tüm diller). `CmsParentSegmentResolver` tam public yolu `prefix + slug_leaf` olarak üretir; `um_cms_url_routes.normalized_path` **tam yol** olarak kalır (çözümleyici/çakışma için). **Panel:** `GET …/parent-segments` (`ParentSegmentsToolController`, Inertia `ParentSegments.vue`); mutasyonlar oturumlu `web` rotaları: `POST/PATCH/DELETE …/parent-segments` (`Cms` modülü web group). **API:** `GET/POST/PATCH/DELETE …/cms/parent-segments` (`ParentSegmentController`, `api.php`). `GET …/cms/routing-meta` → `parent_segment_prefixes` (ör. `Page` sınıfı için locale → prefix haritası). Sidebar: `config/defers/navigation.php` → `_cms_parent_segments` (`admin.system.cms.parentSegments.tool`).

**Nested public paths:** `CmsPublicPathHierarchy` + `CmsUrlRouteRegistry::nestedPublicPagePathWarnings` — üst/alt segment örtüşmesi **uyarı** (slug validate API + `Slug.vue`). Aynı `locale + path` için başka sayfa/redirect kaydı: `isPathClaimedByOther` → slug doğrulama **hata** (kayıt öncesi).

**Promotion (`modularous.cms_promotion`)** — `config/merges/cms_promotion.php`: `enabled`, `scope.*`, `approval.*`. **Dry-run / execute:** `CmsPromotionService` — tek ortamda tablo özetleri; isteğe bağlı **ikinci DB connection** (`compare.connection`) ile salt okunur **sayı farkları** (`diff.comparison.count_delta`); tam veri kopyası yok. `compare.allowed_connections` ile hedef connection beyaz listesi. **Execute:** Modularous cache flush; isteğe bağlı `execute.flush_laravel_cache`; `CmsPromotionExecuted` event; `CmsPromotionScopeApplierInterface` (varsayılan no-op, binding ile genişletilir); `audit.activity_log` + `audit.log_channel`. **Job:** `PromoteCmsReleaseJob` — payload’da `user_id` ile audit. Gerçek ortamlar arası veri aktarımı ayrı pipeline / export-import ile yapılır. **Panel POST + step-up:** session web rotalarında güvenlik açıksa `modularous.security.step_up` — promotion için capability ipucu `promotion.execute`, Site SEO kaydı için `site_seo.edit`; Vue tarafında `useStepUpAwareJsonPost` 428 + modal ile `useForm` ile aynı UX’i hedefler.
