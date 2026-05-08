---
sidebarPos: 2
sidebarTitle: Sync States
---

# Sync States

> Sync a stateable model's states.

## Command Information

- **Signature:** `modularity:sync:states [<model>]`
- **Category:** Sync

## Examples

### Interactive — select a model from a prompt

```bash
php artisan modularity:sync:states
```

### Sync states for a specific model class

```bash
php artisan modularity:sync:states "App\Models\Order"
```

`modularity:sync:states`
------------------------

Finds all models that use the `HasStateable` trait and syncs their state definitions to the database. When no `model` argument is given, an interactive prompt lets you pick from all discovered stateable models. New states are created and reported; existing states are left unchanged.

### Usage

* `modularity:sync:states [<model>]`

### Arguments

#### `model`

Fully qualified class name of the model to sync states for. If omitted, an interactive prompt is shown listing all models that use `HasStateable`.

* Is required: no
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
