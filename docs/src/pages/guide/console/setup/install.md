# Install

> Install unusual-modularous into your Laravel application

## Command Information

- **Signature:** `modularous:install [-d|--default] [-db|--db-process] [-T|--addTranslation] [-M|--addMedia] [-F|--addFile] [-P|--addPosition] [-S|--addSlug] [--addPrice] [-A|--addAuthorized] [-FP|--addFilepond] [--addUuid] [-SS|--addSnapshot]`
- **Category:** Setup


## Examples

### Basic Usage

```bash
php artisan modularous:install
```

### With Options

```bash
# Using shortcut
php artisan modularous:install -d

# Using full option name
php artisan modularous:install --default
```

```bash
# Using shortcut
php artisan modularous:install -db

# Using full option name
php artisan modularous:install --db-process
```

```bash
# Using shortcut
php artisan modularous:install -T

# Using full option name
php artisan modularous:install --addTranslation
```

```bash
# Using shortcut
php artisan modularous:install -M

# Using full option name
php artisan modularous:install --addMedia
```

```bash
# Using shortcut
php artisan modularous:install -F

# Using full option name
php artisan modularous:install --addFile
```

```bash
# Using shortcut
php artisan modularous:install -P

# Using full option name
php artisan modularous:install --addPosition
```

```bash
# Using shortcut
php artisan modularous:install -S

# Using full option name
php artisan modularous:install --addSlug
```

```bash
php artisan modularous:install --addPrice
```

```bash
# Using shortcut
php artisan modularous:install -A

# Using full option name
php artisan modularous:install --addAuthorized
```

```bash
# Using shortcut
php artisan modularous:install -FP

# Using full option name
php artisan modularous:install --addFilepond
```

```bash
php artisan modularous:install --addUuid
```

```bash
# Using shortcut
php artisan modularous:install -SS

# Using full option name
php artisan modularous:install --addSnapshot
```


`modularous:install`
--------------------

Install unusual-modularous into your Laravel application

### Usage

* `modularous:install [-d|--default] [-db|--db-process] [-T|--addTranslation] [-M|--addMedia] [-F|--addFile] [-P|--addPosition] [-S|--addSlug] [--addPrice] [-A|--addAuthorized] [-FP|--addFilepond] [--addUuid] [-SS|--addSnapshot]`

Install unusual-modularous into your Laravel application

### Options

#### `--default|-d`

Use default options for super-admin authentication configuration

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--db-process|-db`

Only handle database configuration processes

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--addTranslation|-T`

Whether model has translation trait or not

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--addMedia|-M`

Do you need to attach images on this module?

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--addFile|-F`

Do you need to attach files on this module?

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--addPosition|-P`

Do you need to manage the position of records on this module?

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--addSlug|-S`

Whether model has sluggable trait or not

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--addPrice`

Whether model has pricing trait or not

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--addAuthorized|-A`

Authorized models to indicate scopes

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--addFilepond|-FP`

Do you need to attach fileponds on this module?

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--addUuid`

Do you need to attach uuid on this module route?

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--addSnapshot|-SS`

Do you need to attach snapshot feature on this module route?

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
