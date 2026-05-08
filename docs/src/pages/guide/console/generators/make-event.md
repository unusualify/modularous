# Make Event

> Create a Laravel Event.

## Command Information

- **Signature:** `modularity:make:event <name> [<module>] [--self] [-f|--force] [--should-broadcast] [--should-broadcast-now] [--should-dispatch-after-commit]`
- **Category:** Generators

## Examples

### Create an event in the default app path

```bash
php artisan modularity:make:event OrderShipped
```

### Create an event inside a module

```bash
php artisan modularity:make:event OrderShipped Shop
```

### Create a broadcastable event

```bash
php artisan modularity:make:event OrderShipped --should-broadcast
```

### Create an event that broadcasts immediately (no queue)

```bash
php artisan modularity:make:event OrderShipped --should-broadcast-now
```

### Create an event that dispatches after database commit

```bash
php artisan modularity:make:event OrderShipped --should-dispatch-after-commit
```

`modularity:make:event`
-----------------------

Scaffolds a new Laravel Event class. When abstract event classes are found in `app/Events/`, the package's own `src/Events/`, or any module's Events directory, an **interactive prompt** lets you optionally extend one of them.

If `--should-broadcast` or `--should-broadcast-now` is passed, additional prompts collect the queue connection name, queue name, and broadcast channel type and name.

Output path resolution:
- No `module` and no `--self` → `app/Events/`
- `module` given → module's Events directory
- `--self` flag → `packages/modularous/src/Events/` (dev only, not allowed in production)

### Usage

* `modularity:make:event <name> [<module>] [--self] [-f|--force] [--should-broadcast] [--should-broadcast-now] [--should-dispatch-after-commit]`

### Arguments

#### `name`

The name of the event class.

* Is required: yes
* Is array: no
* Default: `NULL`

#### `module`

The module to create the event in. If omitted, the event is created in `app/Events/`.

* Is required: no
* Is array: no
* Default: `NULL`

### Options

#### `--self`

Create the event inside the Modularous package source (`src/Events/`). Dev use only.

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

#### `--should-broadcast`

Implement `ShouldBroadcast` — event is dispatched to a queue before broadcasting.

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--should-broadcast-now`

Implement `ShouldBroadcastNow` — event broadcasts synchronously without a queue.

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--should-dispatch-after-commit`

Implement `ShouldDispatchAfterCommit` — event is dispatched only after the current database transaction commits.

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
