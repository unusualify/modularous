# Middleware ve servisler — özet

Bu dosya, Modularity / CMS tarafında öne çıkan **middleware** ve **servislerin** ne işe yaradığını tek yerde toplar. (Yönetim paneli kullanıcıları ve veri modeli odaklı; ziyaretçi tarafında henüz tamamlanmamış parçalar aşağıda not edilir.)

---

## 1. Güvenlik (core — `src/`)

`SecurityServiceProvider` yalnızca `modularityConfig('security.enabled')` açıkken devreye girer. Varsayılan panel route yığınına üç alias eklenir:

| Alias | Sınıf | Amaç |
|--------|--------|------|
| `modularity.security.session` | `SessionSecurityMiddleware` | Oturum **idle timeout** (yapılandırılabilir dakika). Süre aşılırsa çıkış + oturum invalidate; JSON isteklerinde 401, formda login’e yönlendirme. Her istekte `security_last_seen_at` güncellenir. |
| `modularity.security.require_mfa` | `RequireMfaMiddleware` | Kullanıcının rolü **MFA gerektiriyorsa** ve MFA etkin değilse isteği keser (JSON’da 403; web’de logout + login formu). |
| `modularity.security.step_up` | `StepUpMiddleware` | `security.step_up.enabled` açıksa, route **capability** ile eşleşiyorsa ve kullanıcı son doğrulamayı TTL içinde yapmamışsa `StepUpService` ile akışı keser (ek doğrulama). TTL session’da `security_step_up_verified_at` ile tutulur. |

**İlişkili servisler (özet):** `SecurityService` (MFA / step-up eşleştirme), `StepUpService` (kesinti / doğrulama UI’si). Detay: capability tabanlı route eşlemesi, cache vb. `HANDOFF.md` ve güvenlik dokümantasyonu.

---

## 2. CMS modülü — URL ve yönlendirme verisi (`modules/Cms/`)

### 2.1 `CanonicalUrlResolver` (+ arayüz `CanonicalUrlResolverInterface`)

- **Ne işe yarar:** Gelen path’i normalize eder (slash, küçük harf, trailing slash vb. — `cms_seo.canonical.*` config).
- **`resolve()`:** Host + path + locale ile **kanonik URL** üretir; `cms_routing.redirect_to_canonical` ile “gelen URL kanonik değilse 301 ile düzelt” mantığını hesaplar.
- **Kullanım yeri:** `CanonicalLocaleMiddleware`, redirect doğrulama, `CmsUrlRouteRegistry` path karşılaştırmaları.

### 2.2 `RedirectValidationService` (+ `RedirectValidationServiceInterface`)

- **Ne işe yarar:** Panelden kaydedilecek **site yönlendirme kuralları** (`from_path` → `to_path`) için sunucu tarafı kontrol.
- **Kontroller (özet):** Aynı path’e işaret etme, aktif sayfa path’i ile çakışma (locale bazlı `active_paths`), mevcut kurallarla **döngü**, isteğe bağlı **cross-locale uyarısı** (bloklamaz, `warnings` döner).
- **Kullanım yeri:** `RedirectController` (store/update); hatalar validation exception, uyarılar JSON’a veya session flash + Inertia `flash` ile taşınır.

### 2.3 `CmsUrlRouteRegistry` (+ core `PublicUrlRegistryContract`)

- **Ne işe yarar:** `UrlRoute` tablosunu **Page** public path’leri ve **Redirect** kaynak path’leri ile senkron tutar (çakışma önleme / tek kaynak registry). Uygular: `Unusualify\Modularity\Contracts\PublicUrlRegistryContract` (container’da bu arayüze `CmsUrlRouteRegistry` bağlanır).
- **Ne zaman:** `Modules\Cms\Repositories\Traits\UrlRouteRegistrySyncTrait` → core `PublicUrlRegistrySyncDispatchTrait` ile sınıf bazlı handler haritası; repository `afterSave` / `afterDelete` / `afterRestore`.
- **Slug doğrulama (panel):** `ExtendsSlugValidationWithPublicUrlRegistry` (`src/Services/Concerns/`) — `CmsSlugInputValidationService` içinde kullanılır; başka modüller aynı trait + kendi `PublicUrlRegistryContract` implementasyonu ile tekrar kullanabilir.
- **Not:** Bu **veri senkronu**dur; ziyaretçi HTTP isteğinde otomatik 302 üretmek için ayrıca bir middleware/route bağlanması gerekir (aşağıda).

### 2.4 `CanonicalLocaleMiddleware`

- **Ne işe yarar:** `cms_routing.redirect_to_canonical` açıksa, isteğin path’i kanonik değilse **301** ile kanonik adrese yönlendirir (`CanonicalUrlResolver::resolve`).
- **Redirect CRUD ile ilişki:** Doğrudan `um_cms_redirects` satırlarını okumaz; **locale/host canonical** hattıdır.

### 2.5 `CmsVisitorRedirectResolver` + `VisitorRedirectMiddleware`

- **Ne işe yarar:** Panelde kayıtlı **site yönlendirmelerini** (`Redirect` tablosu) gerçek **HTTP isteğine** uygular: eşleşen path için `Location` + yapılandırılan status code (301/302/…).
- **Sıra:** `front.php` içinde `web` → (isteğe bağlı) `CanonicalLocaleMiddleware` → **`VisitorRedirectMiddleware`**.
- **İç path (`CmsFrontPath`):** Modülün public route öneki (ör. `/cms/…`, `cms_routing.front_route_prefix` ile uyumlu) istekten çıkarılır; böylece kayıtlı `UrlRoute` satırları (önek **olmadan**) ile eşleşir.
- **Eşleştirme:** İç path normalize edilir; URL’de locale prefix varsa ayrıştırılır (`/tr/foo` → locale `tr`, iç path `/foo`). Önce **`UrlRoute`** (`kind = redirect_source`) ile indeksli arama; tablo yoksa veya satır yoksa **`Redirect`** modelinde locale + normalize `from_path` ile tarama.
- **Öncelik:** Aynı `locale` + path için **`UrlRoute` `page_public`** (yayın sayfası) varsa yönlendirme **uygulanmaz** (sayfa kazanır).
- **Hariç:** `modularity.admin_app_path` altı (panel), `system_prefix`, `cms_routing.visitor_redirect_exclude_prefixes` (varsayılan: `api`, `sanctum`, `livewire`).
- **Config:** `cms_routing.visitor_redirects_enabled` (varsayılan `true`).

### 2.6 CMS `front.php` — public sayfa (catch-all)

- **Ne zaman:** `cms_routing.public_pages_enabled` açıkken (varsayılan `true`).
- **Rota:** Modül URL öneki altında `GET /{path?}` (`path` = `.*`), isim: `…cms.page` (`curtModuleRouteNamePrefix` ile).
- **`CmsPublicPageResolver`:** `CmsFrontPath::innerNormalizedPath` → `CmsVisitorRedirectResolver::resolveLocaleAndInnerPath` (locale segmenti yoksa *implicit* mod; uzun locale kodları önce eşlenir, örn. `pt-BR` / `pt`) → `UrlRoute` (`page_public`) → **yayınlanmış ve görünür** `Page`. URL’de locale yokken yalnızca varsayılan locale ile eşleşmezse, aynı path için tek kayıt (veya çokluysa `default_locale` öncelikli) ile devam edilir.
- **View:** `cms::page.custom` (public) — `CmsPublicSeo` ile `<title>`, meta description, `<link rel="canonical">`, `meta name="robots"` (çeviri `canonical_url`, `robots_index` / `robots_follow`); yoksa `CanonicalUrlResolver` ile kanonik URL.
- **Config:** `cms_routing.front_route_prefix` (env: `MODULARITY_CMS_FRONT_ROUTE_PREFIX`, varsayılan `cms`) — `UrlRoute` kayıtları bu segmenti **içermez**; ziyaretçi URL’si `/cms/tr/...` olsa bile iç eşleştirme `/tr/...` üzerinden yapılır.

---

## 3. Inertia — paylaşılan flash (`src/Http/Middleware/HandleInertiaRequests.php`)

- **Genel:** Tüm Inertia sayfalarına `auth`, `flash`, `config`, vb. paylaşılır.
- **Uyarılar (stack):** `flash.warnings` — session anahtarı `modularity.flash_warnings` (`Unusualify\Modularity\Support\ModularityFlashWarnings::SESSION_KEY`, tek seferlik `pull`). Birden fazla kaynak aynı istekte `ModularityFlashWarnings::merge()` ile ekleyebilir; Inertia sonraki yüklemede `MainLayout` içinde sırayla toast gösterir. AJAX yanıtlarında aynı anlam `response.data.warnings` dizisiyle taşınır.

---

## 4. Özet tablo: kim, nerede?

| Bileşen | Kimin isteği | Ana sonuç |
|---------|----------------|-----------|
| Session security middleware | Giriş yapmış panel kullanıcısı | Idle’da çıkış |
| Require MFA middleware | Giriş yapmış kullanıcı | MFA zorunluluğu |
| Step-up middleware | Giriş yapmış kullanıcı | Hassas route’da ek doğrulama |
| Canonical locale middleware | **Ziyaretçi / public** (front stack’e eklendiğinde) | Kanonik URL’e 301 |
| Visitor redirect middleware | **Ziyaretçi / public** | CMS `Redirect` kurallarına göre 301/302… |
| `CmsPublicPageResolver` + `PublicPageController` | **Ziyaretçi / public** | Catch-all ile yayın sayfası (Blade) |
| Redirect validation service | Panel formu (POST/PUT) | Kural geçerli mi + uyarılar |
| CMS URL route registry | Arka plan (save/delete) | `UrlRoute` tablosu güncel |
| Inertia flash (`flash.warnings`) | Sonraki sayfa yükü (editör) | Uyarı toast (stack) |

---

## 5. Bilinçli boşluklar (şu anki mimari)

- **Public sayfa şablonu:** Minimal Blade; tema, layout birleştirme ve gelişmiş SEO (Open Graph, yapısal veri) sonraki adımlar.
- **Canonical middleware** ile **CRUD redirect kuralları** farklı problemlerdir; ikisi birbirinin yerine geçmez.

---

*Son güncelleme: 2026-04 — Modularity paketi (`packages/modularous`).*
