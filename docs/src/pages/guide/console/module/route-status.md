# Route Status

> List route enable/disable status per module.

## Command Information

- **Signature:** `modularity:route:status`
- **Category:** Module

## Examples

### List all route statuses

```bash
php artisan modularity:route:status
```

`modularity:route:status`
-------------------------

Outputs a table showing every enabled module alongside each of its routes and whether that route is currently `enabled` or `disabled`. Modules with no tracked routes are shown with a `(no routes tracked)` note.

| Module | Route | Status |
|--------|-------|--------|
| Blog | posts | enabled |
| Blog | categories | disabled |
| Shop | products | enabled |

Use [`modularity:route:enable`](/guide/console/module/route-enable) and [`modularity:route:disable`](/guide/console/module/route-disable) to change a route's status.

### Usage

* `modularity:route:status`

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
