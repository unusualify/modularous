# Sitemap sistemi — gereksinimler ve uygulama planı (taslak)

> **Amaç:** Indexlenebilir sayfaların keşfi için otomatik sitemap, `include/exclude`, `lastmod`, `hreflang`, çok dilli sinyaller.

> **Not (HasPosition):** [`HasPosition`](src/Entities/Traits/HasPosition.php) yalnızca **liste `position` sıralaması** içindir; sitemap `priority` (0.0–1.0) ve `changefreq` alanları **ayrı** tutulmalı (morph pivot veya sitemap item tablosu). Karıştırma.

---

## 1. Mevcut altyapı (referans)

- Çeviri tabanlı **`sitemap_include`**: [`TranslatableMetadata`](src/Support/TranslatableMetadata.php) + [`HasTranslatableMetadata`](src/Entities/Traits/HasTranslatableMetadata.php).
- Public URL: `UrlRoute` + `CmsParentSegmentResolver` + `CmsPublicSiteUrl` / `CmsFrontPath` ile hizalı tam URL üretimi.
- Public görünürlük: `published` + `scopeVisible` ([`HasScopes`](src/Entities/Traits/Core/HasScopes.php)), [`CmsPublicModelResolver`](modules/Cms/Services/CmsPublicModelResolver.php).
- **Merkezi rota defteri:** [`UrlRoute`](modules/Cms/Entities/UrlRoute.php) (`kind`: `KIND_PAGE_PUBLIC` vs. `KIND_REDIRECT_SOURCE`), her satır `locale` + `normalized_path` + morph `urlable`. Model kaydında/restore/silme sonrası [`CmsUrlRouteRegistry`](modules/Cms/Services/CmsUrlRouteRegistry.php) + [`UrlRouteRegistrySyncTrait`](modules/Cms/Repositories/Traits/UrlRouteRegistrySyncTrait.php) ile tablo güncellenir; public catch-all çözümü de [`CmsPublicModelResolver`](modules/Cms/Services/CmsPublicModelResolver.php) üzerinde aynı tabloyu okur. [`CmsFrontRouteRegistrar`](modules/Cms/Routing/CmsFrontRouteRegistrar.php) yalnızca **tek catch-all** kaydı ve controller çözümünü açar; path envanteri veritabanındadır. [`CanonicalUrlResolver`](modules/Cms/Services/CanonicalUrlResolver.php) `cms_routing` ile canonical host/segment kuralını üretir (hreflang / alternates ile tutarlılık için referans).

### 1.1 Sitemap “keşfi”: her alt modülü taramak zorunlu değil

- **Aday URL listesi** için birincil sorgu yüzeyi: `UrlRoute` satırları, filtre: `kind = page_public` (ve gerekiyorsa `locale` / site kapsamı). `with('urlable')` eager load; filtre: model üzerinde `published`, `scopeVisible`, ilgili çeviride `sitemap_include` (bkz. `TranslatableMetadata`).
- **Neden ayrı submodule döngüsü gerekmez:** Aynı tablo, hangi `Studly` entity sınıfı olursa olsun, trait ile senkronize edilen public sayfa yollarını tutar; sitemap jeneratörü **morph `urlable`** üzerinden tip bağımsız ilerler. Yalnızca *hiç* `UrlRoute`’a yazılmayan bir public sayfa tipi varsa (sync dışı) o tip için ayrı keşif veya sync genişletmesi gerekir.
- **Redirect satırları** (`KIND_REDIRECT_SOURCE`): indexlenebilir “sayfa” sitemap’inden hariç; yalnızca ürün kararına göre ayrı politika.
- **Mutlak URL:** mevcut `CmsFrontPath` + `CmsPublicSiteUrl` (ve gerekirse `CanonicalUrlResolver` çıktısı) ile; `UrlRoute` tek başına host içermez, yol + locale taşır.

---

## 2. Hedef davranış

| Konu | İstenen |
|------|--------|
| **Üretim** | Sitemap **generator** + **cache** + **rebuild** (queue job) |
| **hreflang** | Locale eşlemesiyle otomatik (mevcut `CmsLocalizationContract` / path segment locale’leri) |
| **Dahil edilecekler** | `HasParentSegment` kullanan modüllerden: aktif, **public**, **published** + `visible` kapsamında, **bozulmamış** yerelleştirilmiş URL’ler |
| **Dahil / hariç (locale)** | [`HasTranslatableMetadata`](src/Entities/Traits/HasTranslatableMetadata.php) + [`TranslatableMetadata::sitemap_include`](src/Support/TranslatableMetadata.php) — ayrı **`IsSitemaping` trait’i oluşturma**; mevcut çeviri alanı sitemap ilişkisi ve panel switch’i için yeterli kabul edilir. |
| **Alt modül** | Yeni `Cms` → **Sitemap** (veya eşdeğer) alt modülü: sitemap’lenebilir kayıtları toplar / yönetir |

---

## 3. “Kayıt yokken de URL varmış gibi”

- **Yol (path) keşfi** normalde `UrlRoute`’dadır (§1.1). “Sanal” vurgusu burada özellikle **sitemap morph pivot** satırı yokken `changefreq` / `priority` için şema default’larının uygulanması içindir; public path zaten `UrlRoute`’da yoksa o kayıt sitemap’e giremez.
- **Generate** aşamasında, morph pivot’da satır olmasa bile, kaynak model **sitemap kriterlerini** sağlıyorsa (published + visible + `sitemap_include` vb.) URL **UrlRoute + public URL kurallarıyla** türetilip sitemap listesine **sanal** (computed) eklenebilmeli.
- İsteğe bağlı **sync**: `sitemap_include` = true + published + visible kriterini sağlayan `urlable` kayıtlar için pivot satırlarını toplu oluşturma/güncelleme (priority/changefreq persist) — ayrı entity trait şartı yok.

---

## 4. Düzenlenebilir alanlar (pivot / sitemap item)

- **Düzenlenebilir:** `changefreq`, `priority` (XML sitemap anlamında).
- **Düzenlenmez (kaynak):** `lastmod` → ilişkili modelin **`updated_at`** (gerekirse translation güncellemesi için net kural: ana model mı, translation mı — tek kural seçilir).

---

## 5. Dry-run ve cache (commit öncesi)

- Sistem, yeni sitemap **commit** edilene kadar **her zaman önceki commit’lenmiş** sitemap’i / önbelleği servis eder.
- **Dry-run** üretim sonucu canlı yanıta yazılmaz; yalnızca önizleme/validasyon.
- **Commit** sonrası: servis cache’i **atomic** veya “swap” ile günceller.
- **Önbellek:** `Cache::rememberForever` (veya eşdeğer kalıcı store) + versiyon/etag anahtarı; commit’te yeni veri bu cache’e yazılır. (Fallback: dosya + `public/sitemap.xml` kopyası — ürün kararı.)

---

## 6. Veri modeli: morph pivot

- **Sitemap** (veya `sitemap_runs` / `sitemap_revisions`) ile **urlable** (`morph`) arasında pivot:
  - `sitemapable_type`, `sitemapable_id` (veya tersi: entry → morph hedef)
  - `changefreq`, `priority` (nullable → default şema)
  - İsteğe bağlı: `sitemap_id` / `build_id` sürümleme
- Hedef: tek yerden tüm sitemap’e giren modelleri ilişkilendirmek ve override alanlarını saklamak.

---

## 7. Hreflang

- Aynı kaynak için tüm yayımlı locale’lerde `UrlRoute` + public path ile mutlak URL listesi; `<xhtml:link rel="alternate" hreflang="...">` üretimi.
- Locale listesi: `CmsLocalizationContract::pathSegmentLocales()` / `supportedLocalesMeta` ile uyumlu.

---

## 8. Uygulama maddeleri (sıra önerisi)

1. **Şema:** migration: `sitemaps` (veya tek satırlık “current”), `sitemapables` morph pivot (changefreq, priority).
2. **Dahil etme kuralı (trait yok):** Jeneratör sorgu kapsamı: `UrlRoute` + `urlable` + ilgili çeviride `sitemap_include` ([`TranslatableMetadata`](src/Support/TranslatableMetadata.php) / [`HasTranslatableMetadata`](src/Entities/Traits/HasTranslatableMetadata.php)) + `published` + `scopeVisible`. Repository’de ayrı `SitemapingTrait` veya modelde `IsSitemaping` **tanımlanmaz**; gerekirse sadece mevcut [`TranslatableMetadataTrait`](src/Repositories/Traits/TranslatableMetadataTrait.php) (form) ile hizalanır.
3. **Servis:** `SitemapBuildService` — keşif: önce [`UrlRoute`](modules/Cms/Entities/UrlRoute.php) (`kind = page_public`) + `urlable` (bkz. §1.1); input: dry-run bool; output: XML veya dizi; **commit** ayrı metot.
4. **Cache servisi:** `SitemapCacheService` — `rememberForever` + commit’te swap; 404/503 yok, eski sitemap canlı kalsın.
5. **Job:** `RebuildSitemapJob` (queue) — ağır toplu üretim.
6. **Route:** `GET /sitemap.xml` (veya index + parçalar) — cache’ten servis; dry-run sadece admin.
7. **Cms Sitemap modülü:** panel’de dry-run sonuç, commit, isteğe bağlı “sync pivot” toplu işlem.
8. **Testler:** dry-run, commit swap, hreflang; ParentSegment’li URL birimi.

---

## 9. Bağımlılıklar

- Mevcut `UrlRoute` ve public URL bütünlüğü; kırık URL’lerin listelenmemesi (mevcut resolver ile uyum).
- `TranslatableMetadata::sitemap_include` false olan locale satırları hariç.

---

## 10. Sonraki agent — uygulama todo listesi (handoff)

**Önce oku:** §1–§9. **Sabit kararlar:** (1) Keşif **submodule döngüsü değil**, [`UrlRoute`](modules/Cms/Entities/UrlRoute.php) (`kind = page_public`) + `urlable` (§1.1). (2) **Yeni `IsSitemaping` / `SitemapingTrait` yok**; locale dahil/hariç = [`TranslatableMetadata::sitemap_include`](src/Support/TranslatableMetadata.php) + [`HasTranslatableMetadata`](src/Entities/Traits/HasTranslatableMetadata.php) (§2, §8.2). (3) [`HasPosition`](src/Entities/Traits/HasPosition.php) ≠ XML `priority` (üst not).

| # | Görev | Notlar |
|---|--------|--------|
| 1 | Migration: `sitemaps` (veya tek “current” revision) + `sitemapables` morph pivot (`changefreq`, `priority` nullable) | §6 |
| 2 | `lastmod` kuralını kod + dokümanda netleştir (ana model `updated_at` vs translation — tek kural) | §4 |
| 3 | `SitemapBuildService` | Sorgu: `UrlRoute` + `with('urlable')`; filtre: `published`, `scopeVisible`, çeviride `sitemap_include`; `KIND_REDIRECT_SOURCE` hariç; mutlak URL: `CmsFrontPath` + `CmsPublicSiteUrl` / `cms_routing` | §1.1, §3 |
| 4 | Hreflang / `xhtml:link` alternates | Aynı `urlable` için locale başına `UrlRoute` satırları; [`CmsLocalizationContract`](modules/Cms/Contracts/CmsLocalizationContract.php) ile uyum | §7 |
| 5 | `SitemapCacheService` | `Cache::rememberForever` (veya eşdeğer) + **commit** ile atomic/swap; canlı sitemap boş/503 olmamalı | §5 |
| 6 | `RebuildSitemapJob` (queue) | §8 |
| 7 | Public route `GET /sitemap.xml` (gerekirse index + parçalar) | Önbellekten servis; dry-run yalnız admin/internal | §8 |
| 8 | Cms **Sitemap** alt modülü (panel) | Dry-run sonuç, commit, isteğe bağlı pivot “sync” toplu işlem | §2, §8 |
| 9 | Testler | Dry-run vs commit, cache swap, hreflang, en az bir ParentSegment’li URL birimi | §8 |
| 10 | Ship sonrası | [`HANDOFF.md`](../HANDOFF.md) Faz 7 satırı + ilgili changelog güncelle |

**İlgili modül dosyaları (başlangıç):** `modules/Cms/Services/CanonicalUrlResolver.php`, `CmsUrlRouteRegistry.php`, `CmsPublicModelResolver.php`, `Routing/CmsFrontRouteRegistrar.php` — jeneratör aynı URL semantiğini korumalı.

### 10.1 Uygulama (paket — 2026-04-02)

- **Servis sınıf adı:** `CmsSitemapBuildService` (plan maddesindeki `SitemapBuildService` ile aynı rol).
- **Şema:** `2026_04_02_000001_create_cms_sitemap_tables.php` → `um_cms_sitemaps` (id=1 `default` seed) + `um_cms_sitemapables` (morph + `changefreq` / `priority`).
- **Cache / route / job / artisan:** `CmsSitemapCacheService` (`Cache::forever`); `GET /sitemap.xml` → `PublicSitemapController`; `RebuildCmsSitemapJob`; `php artisan cms:sitemap:rebuild` (`--dry-run` ile stdout).
- **Config:** `config/merges/cms_sitemap.php` (`modularity.cms_sitemap`); açıklama: [`docs/CONFIG.md`](../docs/CONFIG.md) (Sitemap bölümü).
- **Kalan (ürün / panel):** Cms Sitemap **alt modül** UI (dry-run/commit ekranı), parça sitemap index; isteğe bağlı sitemap’i `robots.txt` / Site SEO’ya bağlama.

---

*Bu belge plan iterasyonudur; onay sonrası implementasyona ayrı PR ile gidilir. §10, sonraki agent için checklist olarak güncellenir.*
