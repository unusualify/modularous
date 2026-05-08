# Make Operation

> Create a one-time operation file with the Modularous tag.

## Command Information

- **Signature:** `modularity:make:operation <name> [--self] [--path[=PATH]] [-t|--tag[=TAG]] [--async] [--queue[=QUEUE]]`
- **Aliases:** `modularity:operations:make`, `modularity:create:operation`, `mod:c:operation`
- **Category:** Generators

> **Requires:** [`timokoerber/laravel-one-time-operations`](https://github.com/TimoKoerber/laravel-one-time-operations) package.

## Examples

### Create an operation in the default operations directory

```bash
php artisan modularity:make:operation SeedNewPermissions
```

### Create an operation with a custom tag

```bash
php artisan modularity:make:operation SeedNewPermissions --tag=permissions
```

### Create an asynchronous operation on a specific queue

```bash
php artisan modularity:make:operation SeedNewPermissions --async --queue=operations
```

### Create an operation at a custom path

```bash
php artisan modularity:make:operation SeedNewPermissions --path=database/operations
```

`modularity:make:operation`
---------------------------

Scaffolds a new one-time operation file using the `timokoerber/laravel-one-time-operations` package. The generated filename includes a timestamp prefix and an `_operation` suffix (e.g. `2026_04_10_120000_seed_new_permissions_operation.php`). The default output directory is read from `config('one-time-operations.directory')`, typically `operations/`.

### Usage

* `modularity:make:operation <name> [--self] [--path[=PATH]] [-t|--tag[=TAG]] [--async] [--queue[=QUEUE]]`
* `modularity:operations:make <name>`
* `modularity:create:operation <name>`
* `mod:c:operation <name>`

### Arguments

#### `name`

The name of the operation.

* Is required: yes
* Is array: no
* Default: `NULL`

### Options

#### `--self`

Create the operation inside the Modularous package source (`operations/`). Tags it as `modularity` automatically. Dev use only.

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--path`

Custom output directory for the operation file. Relative paths are resolved from the project root.

* Accept value: yes
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--tag|-t`

Tag to assign to the operation. Used by the one-time-operations runner to filter which operations to process.

* Accept value: yes
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--async`

Generate the operation as asynchronous (processed via a queue job).

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--queue`

The queue to dispatch the asynchronous operation to.

* Accept value: yes
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `default`

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
