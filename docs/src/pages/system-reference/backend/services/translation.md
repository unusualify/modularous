---
sidebarPos: 20
sidebarTitle: Translation
---

# Translation

**File**: `src/Services/Translation.php`
**Namespace**: `JoeDixon\Translation\Drivers`

Abstract base class for every translation driver in the `joedixon/laravel-translation` package. Modularous ships a local copy of this file so it can fine-tune the shared driver contract (scanning, missing-key discovery, source-language merge) without forking the upstream package. Concrete drivers — most importantly [`FileTranslation`](./file-translation) — extend this class and implement the storage backend.

The file lives under Modularous `src/Services/` tree but declares the upstream namespace so Laravel's autoloader picks it up in place of the vendored version. Only the contract below is Modularous-owned; downstream drivers define `$scanner`, `allTranslationsFor()`, `allLanguages()`, `addGroupTranslation()`, and `addSingleTranslation()`.

## Class Signature

```php
abstract class Translation
{
    public function findMissingTranslations($language);
    public function saveMissingTranslations($language = false);
    public function getSourceLanguageTranslationsWith($language);
    public function filterTranslationsFor($language, $filter);
    public function add(Request $request, $language, $isGroupTranslation);
}
```

## Methods

### `findMissingTranslations($language): array`

Returns every translation key discovered by `$this->scanner->findTranslations()` that has no value in `$language`. Uses `array_diff_assoc_recursive()` to compare the codebase-scanned tree against the persisted tree for the given locale.

### `saveMissingTranslations($language = false): void`

Walks one locale (when `$language` is truthy) or every locale returned by `allLanguages()` and persists blank entries for every missing key. Branches on the group name:

- Groups containing the substring `single` → `addSingleTranslation($language, $group, $key)` (JSON file)
- Everything else → `addGroupTranslation($language, $group, $key)` (PHP array file)

Used by translation-sync tooling to backfill missing keys before syncing values.

### `getSourceLanguageTranslationsWith($language): Collection`

Merges the source-locale tree (`app()->config['app']['locale']`) with the `$language` tree, producing a nested `Collection<type, Collection<group, array<key, [sourceLocale, $language]>>>`. The resulting shape is designed for side-by-side diff UIs — each key maps to `[source => value, target => value]`.

### `filterTranslationsFor($language, $filter): Collection`

Takes the merged collection from `getSourceLanguageTranslationsWith()` and filters entries whose **group name**, **key**, **source-locale value**, or **target-locale value** contains `$filter`. Groups with no surviving keys are dropped. Returns the full merged collection unchanged when `$filter` is empty.

### `add(Request $request, $language, $isGroupTranslation): void`

Adds a single translation entry from an HTTP request:

```php
$namespace = $request->has('namespace') && $request->get('namespace') ? "{$request->get('namespace')}::" : '';
$group     = $namespace . $request->get('group');
$key       = $request->get('key');
$value     = $request->get('value') ?: '';
```

- When `$isGroupTranslation` → `addGroupTranslation($language, $group, $key, $value)`
- Otherwise → `addSingleTranslation($language, 'single', $key, $value)`

Dispatches a `JoeDixon\Translation\Events\TranslationAdded` event afterwards (`$group ?: 'single'` as the group label). Listeners can react by warming caches, regenerating front-end bundles, or broadcasting to other locales.

## Abstract Hooks (implemented by drivers)

The base class calls these but does not define them — consult the concrete driver for semantics:

| Hook | Purpose |
|------|---------|
| `allTranslationsFor($language): Collection` | Load the persisted tree for one locale |
| `allLanguages(): Collection` | List every locale with at least one translation file |
| `addGroupTranslation($language, $group, $key, $value = '')` | Persist a key in a PHP group file |
| `addSingleTranslation($language, $group, $key, $value = '')` | Persist a key in a JSON single-file translation |
| `$scanner` | Object providing `findTranslations(): array` — walks source files looking for `__()`/`trans()`/`@lang()` calls |

## Related

- [FileTranslation](./file-translation) — the file-based driver that extends this class and adds cross-path sync
- [`modularous:sync:translations`](/guide/console/sync/sync-translations) — Artisan command that drives the sync flow
- [FileLoader](../support/file-loader) — multi-path Laravel translation loader that cooperates with `FileTranslation`
