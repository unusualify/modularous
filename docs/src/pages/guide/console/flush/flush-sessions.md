---
sidebarPos: 4
sidebarTitle: Flush Sessions
---

# Flush Sessions

> Flush all user sessions.

## Command Information

- **Signature:** `modularity:flush:sessions [--driver[=DRIVER]]`
- **Alias:** `modularity:session:flush`
- **Category:** Flush

## Examples

### Flush sessions using the app's configured driver

```bash
php artisan modularity:flush:sessions
```

### Flush database sessions explicitly

```bash
php artisan modularity:flush:sessions --driver=database
```

### Flush file sessions explicitly

```bash
php artisan modularity:flush:sessions --driver=file
```

### Flush both database and file sessions

```bash
php artisan modularity:flush:sessions --driver=all
```

`modularity:flush:sessions`
---------------------------

Clears all active user sessions. The driver is read from `config('session.driver')` when `--driver` is not specified. Supports `database` (truncates the sessions table), `file` (deletes all files in the session path), and `all` (both).

### Usage

* `modularity:flush:sessions [--driver[=DRIVER]]`
* `modularity:session:flush [--driver[=DRIVER]]`

### Options

#### `--driver`

The session driver to flush. Reads from `session.driver` config when omitted.

* Accept value: yes
* Is value required: no
* Is multiple: no
* Is negatable: no
* Allowed values: `database`, `file`, `all`
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
