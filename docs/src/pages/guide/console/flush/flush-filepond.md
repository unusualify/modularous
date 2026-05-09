---
sidebarPos: 3
sidebarTitle: Flush Filepond
---

# Flush Filepond

> Flush temporary FilePond uploads.

## Command Information

- **Signature:** `modularous:flush:filepond [<days>]`
- **Alias:** `modularous:filepond:flush`
- **Category:** Flush

## Examples

### Flush filepond files older than 7 days (default)

```bash
php artisan modularous:flush:filepond
```

### Flush filepond files older than 3 days

```bash
php artisan modularous:flush:filepond 3
```

### Flush all filepond files (0 days)

```bash
php artisan modularous:flush:filepond 0
```

`modularous:flush:filepond`
---------------------------

Deletes temporary FilePond upload files that are older than the specified number of days, then clears empty FilePond staging folders. Useful as a scheduled task to prevent disk bloat from abandoned uploads.

### Usage

* `modularous:flush:filepond [<days>]`
* `modularous:filepond:flush [<days>]`

### Arguments

#### `days`

The number of days to keep temporary FilePond files. Files older than this are deleted.

* Is required: no
* Is array: no
* Default: `7`

### Options

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
