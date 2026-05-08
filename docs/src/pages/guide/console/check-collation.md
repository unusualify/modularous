# Check Collation

> Check database and connection collations.

## Command Information

- **Signature:** `modularity:db:check-collation <table>`
- **Category:** Console

## Examples

### Check collation for a specific table

```bash
php artisan modularity:db:check-collation users
```

### Check collation for a module's main table

```bash
php artisan modularity:db:check-collation posts
```

`modularity:db:check-collation`
--------------------------------

Outputs the current database collation, the active connection collation, and then a per-column collation listing for the given table. Useful for diagnosing charset mismatches that cause query errors when joining tables with different collations.

**Example output:**

```
Database Collation: utf8mb4_unicode_ci
Connection Collation: utf8mb4_unicode_ci

users table columns:
id: NULL
name: utf8mb4_unicode_ci
email: utf8mb4_unicode_ci
```

### Usage

* `modularity:db:check-collation <table>`

### Arguments

#### `table`

The name of the database table to inspect column collations for.

* Is required: yes
* Is array: no
* Default: `NULL`

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
