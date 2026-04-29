---
sidebarPos: 11
sidebarTitle: RegexReplacement
---

# RegexReplacement

`Unusualify\Modularity\Support\RegexReplacement`

Recursively walks a directory tree and applies a `preg_replace()` substitution to every file that matches a glob-style directory pattern. Supports dry-run preview, verbosity levels, and vendor/node_modules safety guards.

## Constructor

```php
new RegexReplacement(
    string $path,               // root directory to walk
    string $pattern,            // PCRE regex to find
    string $data,               // replacement string
    string $directory_pattern = '**/*.php',  // glob pattern for file selection
    bool   $quiet = false,
    mixed  $verbose = null,
    bool   $test = false,       // dry-run mode
)
```

## Configuration Methods

| Method | Description |
|--------|-------------|
| `setPath(string $path)` | Change the root directory |
| `setPattern(string $pattern)` | Change the PCRE search pattern |
| `setData(string $data)` | Change the replacement string |
| `setDirectoryPattern(string $pattern)` | Change the file glob pattern |

## Running

### `run(): bool`

Walk the directory tree, collect matching files, and apply `preg_replace($pattern, $data, $content)` to each one.

In **dry-run mode** (`$test = true`) no files are written. Instead, `displayPatternMatches()` is called for each file to print a coloured preview.

**Safety guards:**

- Throws `\Exception` if `$path` is empty or `/`.
- Skips files inside `vendor/` or `node_modules/` unless the base `$path` is itself inside one of those directories.

### `replacePatternFile(string $file): bool`

Apply the replacement to a single file. Skipped if `pretending()` is true.

### `displayPatternMatches(string $file): void`

Print a coloured diff of matched lines to stdout. Controlled by verbosity level:

- **Quiet** — no output.
- **Normal** — file name only.
- **Verbose** (`-v`) — file name + matched lines with context.
- **Very Verbose** (`-vv`) — file name + unified diff of original vs. replaced lines.

## Example

```php
use Unusualify\Modularity\Support\RegexReplacement;

// Replace all occurrences of the old namespace in PHP files
$replacement = new RegexReplacement(
    path: app_path(),
    pattern: '/Acme\\\\OldPackage/',
    data: 'Acme\\\\NewPackage',
    directory_pattern: '**/*.php',
    test: true,   // preview first
);

$replacement->run();
```

## Related

- [modularity:update:laravel:configs](/guide/console/update/update-laravel-configs) — uses `RegexReplacement` internally to patch config files
