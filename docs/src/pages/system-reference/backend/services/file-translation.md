---
sidebarPos: 15
sidebarTitle: FileTranslation
---

# FileTranslation

**File**: `src/Services/FileTranslation.php`
**Namespace**: `Unusualify\Modularity\Services`
**Extends**: `JoeDixon\Translation\Drivers\File`

Concrete file-based translation driver used by Modularous. Adds **cross-path sync** on top of the upstream `File` driver — comparing translation files in two different directories and copying missing keys from one to the other. Powers the [`modularity:sync:translations`](/guide/console/sync/sync-translations) Artisan command, which pushes the package's bundled language files into the host application's `lang/` directory.

Its transitive parent, [`Translation`](./translation), supplies scanning and source-locale merge utilities; this class focuses on file I/O and diff.

## Class Signature

```php
class FileTranslation extends File
{
    public $disk;
    public $languageFilesPath;
    public $sourceLanguage;
    public $scanner;

    public function __construct(Filesystem $disk, $languageFilesPath, $sourceLanguage, $scanner);
}
```

The constructor re-exposes the parent's four dependencies as public properties so the instance can spawn temporary clones pointing at different `$languageFilesPath` values (used throughout the cross-path helpers).

## Language Listing

| Method | Signature | Returns |
|--------|-----------|---------|
| `getLanguagesExcept` | `(array $languages): array` | All available languages **minus** the given list |
| `getLanguagesOnly` | `(array $languages): array` | **Intersection** of available languages and the given list |

Both wrap `parent::allLanguages()` with `array_diff` / `array_intersect`.

## Cross-Path Helpers

All of the following spawn ephemeral `FileTranslation` instances bound to arbitrary paths — this is the pattern that makes package ↔ app file sync possible without reconfiguring the Laravel service container.

### `getTranslationsFromPath($languageFilesPath, $language): Collection`

```php
$tempInstance = new static($this->disk, $languageFilesPath, $this->sourceLanguage, $this->scanner);
return $tempInstance->allTranslationsFor($language);
```

Loads all translations from an arbitrary path for one language. No global state is mutated — the temporary instance is discarded after the call.

### `findMissingKeysFromPath($sourcePath, $targetPath, $language): array`

Diffs two paths for a single language. Delegates to `compareTranslations()` internally and returns a `[type => [group => [key => value]]]` tree containing only keys present in `$sourcePath` but missing from `$targetPath`.

### `findAllMissingKeys($sourcePath, $targetPath): array`

Iterates every language present in `$sourcePath` (via a throwaway `FileTranslation` rooted there) and returns:

```php
[
    'en' => [ /* missing tree for en */ ],
    'tr' => [ /* missing tree for tr */ ],
    // ... languages with no missing keys are omitted
]
```

### `syncMissingKeysToPath($sourcePath, $targetPath, $language, $missingKeys): void`

Writes the provided missing-keys tree into `$targetPath`. Branches by type:

- `type === 'single'` → `syncSingleTranslations()` (JSON)
- otherwise → `syncGroupTranslations()` (PHP file)

### `syncAllMissingKeys($sourcePath, $targetPath): array`

End-to-end sync for every language. Returns a stats array:

```php
[
    'languages' => [
        'en' => 12,   // 12 keys synced for English
        'tr' => 5,    // 5 keys synced for Turkish
    ],
    'total_keys' => 17,
]
```

This is the method called by `modularity:sync:translations`.

## Internal Sync Helpers

Protected methods invoked by `syncMissingKeysToPath()`:

| Method | Role |
|--------|------|
| `compareTranslations(Collection $source, Collection $target): array` | Nested loop that builds the missing-keys tree — `$target->get($type)->get($group)->has($key)` misses become entries in `$missing` |
| `syncGroupTranslations($targetInstance, $language, $group, $translations)` | Loads existing target group translations, merges the new values, and calls `saveGroupTranslations()` |
| `syncSingleTranslations($targetInstance, $language, $group, $translations)` | Same flow for single (JSON) translations, writing via `saveSingleTranslationsToPath()` |
| `saveSingleTranslationsToPath($targetInstance, $language, $group, Collection $translations)` | Writes JSON to `{languageFilesPath}/vendor/{namespace}/{locale}.json` when the group is namespaced (`{namespace}::single`), else `{languageFilesPath}/{locale}.json`. Creates the directory (`0755`) when missing and uses `JSON_UNESCAPED_UNICODE \| JSON_PRETTY_PRINT` |

## Save Paths

### `saveGroupTranslations($language, $group, $translations): void`

Persists a group translation file. Normalises input (`Collection` → array), sorts keys alphabetically (`ksort`), and expands dot-notation keys back into a nested array via `array_undot()`.

Two write paths:

| Group format | Output path |
|--------------|-------------|
| Plain (`messages`) | `{languageFilesPath}/{locale}/{group}.php` |
| Namespaced (`admin::users`) | `{languageFilesPath}/vendor/{namespace}/{locale}/{group}.php` (via `saveNamespacedGroupTranslations`) |

Both branches use `php_array_file_content($translations)` to render the PHP array.

### `saveNamespacedGroupTranslations($language, $group, $translations): void` _(private)_

Splits the namespaced group on `::`, ensures the vendor language directory exists, and writes the translation array to the resolved path with `php_array_file_content()`.

## Translation File Layout

| Type | Storage | Format |
|------|---------|--------|
| Group | `lang/{locale}/{group}.php` | PHP array |
| JSON (single) | `lang/{locale}.json` | JSON object |
| Namespaced group | `lang/vendor/{namespace}/{locale}/{group}.php` | PHP array |
| Namespaced JSON | `lang/vendor/{namespace}/{locale}.json` | JSON object (written by `saveSingleTranslationsToPath()` when the group is `{namespace}::single`) |

## Related

- [Translation](./translation) — abstract base class (scanning, source-locale merge, `add()`)
- [`modularity:sync:translations`](/guide/console/sync/sync-translations) — command that drives `syncAllMissingKeys()`
- [FileLoader](../support/file-loader) — multi-path translation loader that reads files written by this driver
