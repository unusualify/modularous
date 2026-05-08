# Create Database

> Create the database if it does not exist.

## Command Information

- **Signature:** `modularity:create:database [--connection[=CONNECTION]]`
- **Category:** Setup

## Examples

### Create the default database

```bash
php artisan modularity:create:database
```

### Create a database on a specific connection

```bash
php artisan modularity:create:database --connection=mysql
php artisan modularity:create:database --connection=pgsql
```

`modularity:create:database`
-----------------------------

Creates the database defined in the given connection config if it does not already exist. The database name, host, port, charset, and collation are all read from `config('database.connections.<connection>')`. Useful in CI pipelines and initial setup scripts where the database may not yet exist.

**Supported drivers:**

| Driver | Behaviour |
|--------|-----------|
| `mysql` | Runs `CREATE DATABASE IF NOT EXISTS` with charset and collation from config (defaults: `utf8mb4` / `utf8mb4_unicode_ci`) |
| `pgsql` | Creates the database if it does not appear in `pg_database` |
| `sqlite` | Touches the file at the configured path, creating parent directories as needed |
| `sqlsrv` | Runs `CREATE DATABASE` if the name is not in `sys.databases` |

### Usage

* `modularity:create:database [--connection[=CONNECTION]]`

### Options

#### `--connection`

The database connection to use. Reads from `config('database.default')` when omitted.

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
