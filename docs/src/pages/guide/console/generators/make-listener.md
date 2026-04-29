# Make Listener

> Create a Laravel Listener.

## Command Information

- **Signature:** `modularity:make:listener <name> [<module>] [--self] [-f|--force] [--should-queue] [--should-handle-events-after-commit]`
- **Category:** Generators

## Examples

### Create a listener in the default app path

```bash
php artisan modularity:make:listener SendOrderConfirmation
```

### Create a listener inside a module

```bash
php artisan modularity:make:listener SendOrderConfirmation Shop
```

### Create a queued listener

```bash
php artisan modularity:make:listener SendOrderConfirmation --should-queue
```

### Create a listener that handles events after database commit

```bash
php artisan modularity:make:listener SendOrderConfirmation --should-handle-events-after-commit
```

`modularity:make:listener`
--------------------------

Scaffolds a new Laravel Listener class. An **interactive prompt** lets you optionally bind the listener to an existing event class discovered from `app/Events/`, the package's own events, or any module's Events directory.

If `--should-queue` is passed, additional prompts collect the queue connection, queue name, delay in seconds, and max retry attempts, and a `shouldQueue()` method is generated.

Output path resolution:
- No `module` and no `--self` → `app/Listeners/`
- `module` given → module's Listeners directory
- `--self` flag → `packages/modularous/src/Listeners/` (dev only)

### Usage

* `modularity:make:listener <name> [<module>] [--self] [-f|--force] [--should-queue] [--should-handle-events-after-commit]`

### Arguments

#### `name`

The name of the listener class.

* Is required: yes
* Is array: no
* Default: `NULL`

#### `module`

The module to create the listener in. If omitted, the listener is created in `app/Listeners/`.

* Is required: no
* Is array: no
* Default: `NULL`

### Options

#### `--self`

Create the listener inside the Modularous package source (`src/Listeners/`). Dev use only.

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--force|-f`

Overwrite the file if it already exists.

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--should-queue`

Implement `ShouldQueue` — listener is processed asynchronously via a queue. Prompts for connection, queue name, delay, and max tries.

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--should-handle-events-after-commit`

Implement `ShouldHandleEventsAfterCommit` — listener only runs after the current database transaction commits.

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

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
