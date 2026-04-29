---
sidebarPos: 12
sidebarTitle: media
---

# media

**File**: `src/Helpers/media.php`

File and media utility helpers for human-readable size formatting and safe filename generation.

## Functions

### `bytesToHuman`

```php
bytesToHuman(float $bytes): string
```

Converts a raw byte count to a human-readable string with appropriate unit:

```php
bytesToHuman(1536);     // "1.5 Kb"
bytesToHuman(2097152);  // "2 Mb"
```

Units: `B`, `Kb`, `Mb`, `Gb`, `Tb`, `Pb`. Divides by 1024 at each step and rounds to 2 decimal places.

---

### `replaceAccents`

```php
replaceAccents(string $str): string
```

Transliterates accented and non-ASCII characters to their closest ASCII equivalent using `iconv('UTF-8', 'ASCII//TRANSLIT', ...)`. Example: `café` → `cafe`.

---

### `sanitizeFilename`

```php
sanitizeFilename(string $filename): string
```

Produces a safe, lowercase filename suitable for storage and URL usage:

1. Calls `replaceAccents()` to remove diacritics
2. Replaces spaces and `%20` with `-`
3. Removes all characters except alphanumerics, `-`, and `.`
4. Removes all but the last `.` (to keep the extension)
5. Collapses multiple consecutive `-` into one
6. Removes `-` immediately before `.`
7. Lowercases the result

```php
sanitizeFilename('Héllo Wörld (2).JPG');
// → "hello-world-2.jpg"
```
