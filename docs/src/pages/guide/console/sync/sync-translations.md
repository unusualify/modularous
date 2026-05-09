---
sidebarPos: 3
sidebarTitle: Sync Translations
---

# Sync Translations

> Sync missing translation keys from the Laravel lang path to the Modularous lang path.

## Command Information

- **Signature:** `modularous:sync:translations [--dry-run] [--only-languages[=ONLY-LANGUAGES]] [--exclude-languages[=EXCLUDE-LANGUAGES]] [--language[=LANGUAGE]]`
- **Category:** Sync

## Examples

### Sync all missing keys for all languages

```bash
php artisan modularous:sync:translations
```

### Preview missing keys without writing any files

```bash
php artisan modularous:sync:translations --dry-run
```

### Sync only a specific language

```bash
php artisan modularous:sync:translations --language=tr
```

### Sync only specific languages (comma-separated)

```bash
php artisan modularous:sync:translations --only-languages=en,tr,de
```

### Sync all languages except specific ones

```bash
php artisan modularous:sync:translations --exclude-languages=fr,es
```

`modularous:sync:translations`
-------------------------------

Compares translation files in `lang/` (Laravel's lang path) against `modularous/lang/` and copies any missing keys into the Modularous lang path. Language folders that do not yet exist in `modularous/lang/` are created automatically. Use `--dry-run` to inspect what would be synced without modifying any files.

### Usage

* `modularous:sync:translations [--dry-run] [--only-languages[=ONLY-LANGUAGES]] [--exclude-languages[=EXCLUDE-LANGUAGES]] [--language[=LANGUAGE]]`

### Options

#### `--dry-run`

Show missing keys without writing any files.

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--language`

Sync only a single specific language (e.g. `--language=tr`).

* Accept value: yes
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--only-languages`

Comma-separated list of languages to sync (e.g. `--only-languages=en,tr`).

* Accept value: yes
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--exclude-languages`

Comma-separated list of languages to skip (e.g. `--exclude-languages=fr,es`).

* Accept value: yes
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--help|-h`

Display help for the given command. When no command is given display help for the list command

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--ansi|--no-ansi`

Force (or disable --no-ansi) ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: yes
* Default: `NULL`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--env`

The environment the command should run under

* Accept value: yes
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `NULL`
