# Pint

> Format code with Laravel Pint for the specified targets.

## Command Information

- **Signature:** `modularous:pint [--test] [--dirty] [--repair] [-s|--self]`
- **Category:** Other

## Examples

### Format all module files

```bash
php artisan modularous:pint
```

### Check for formatting issues without fixing them

```bash
php artisan modularous:pint --test
```

### Format only files modified since the last commit

```bash
php artisan modularous:pint --dirty
```

### Format the Modularous package source (dev only)

```bash
php artisan modularous:pint --self
```

`modularous:pint`
-----------------

Runs `./vendor/bin/pint` against the modules directory (read from `config('modules.paths.modules')`). With `--self`, it targets the Modularous package source using the package's own `pint.json` config — this is only available in non-production environments.

### Usage

* `modularous:pint [--test] [--dirty] [--repair] [-s|--self]`

### Options

#### `--test`

Check files for formatting issues without making any changes. Exits with a non-zero code if issues are found.

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--dirty`

Only format files that have been modified (not yet committed).

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--repair`

Run Pint in repair mode.

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--self|-s`

Lint the Modularous package source using the package's own `pint.json`. Not allowed in production.

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
