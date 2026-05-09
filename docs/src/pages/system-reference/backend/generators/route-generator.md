---
sidebarPos: 4
sidebarTitle: RouteGenerator
---

# RouteGenerator

**Class**: `Unusualify\Modularous\Generators\RouteGenerator`  
**Source**: `src/Generators/RouteGenerator.php`  
**Extends**: [`Generator`](./generator)  
**Uses**: `ManageNames`

The primary generator — invoked by `modularous:make:route`. Produces the complete set of PHP files for a new module route: config entry, model, migration(s), controllers, repository, form request, translation keys, and Spatie permissions.

## Additional Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$migrate` | `bool` | `true` | Run `modularous:migrate` after generation |
| `$migration` | `bool` | `true` | Generate a migration file |
| `$plain` | `bool` | `false` | Skip file generation; only write the config entry |
| `$type` | `string` | `'web'` | Route type |
| `$schema` | `string` | — | Column definition string (e.g. `title:string,body:text`) |
| `$rules` | `string` | — | Validation rules string (e.g. `title=required\|min:3`) |
| `$relationships` | `string` | — | Pipe-separated relationship schemas |
| `$useDefaults` | `bool` | — | Include default columns (id, timestamps, etc.) |
| `$customModel` | `string\|null` | — | Fully-qualified class name of an existing model to use instead of generating a new one |
| `$traits` | `Collection` | `[]` | Repository/model traits to apply |
| `$tableName` | `string\|null` | — | Override the auto-derived DB table name |

## `generate()` Flow

```
generate()
├── [test mode] runTest() → dry-run output, return 0
└── [normal mode]
    ├── updateConfigFile() or fixConfigFile()
    ├── addLanguageVariable()
    └── [not plain]
        ├── updateRoutesStatuses()    — enable route in module activator
        ├── generateFolders()         — create missing module directories
        ├── generateResources()       — model, migration, controllers, repo, request…
        ├── generateFiles()           — stub-based files (routes, Vue pages, etc.)
        ├── createRoutePermissions()  — Spatie permissions
        └── [migrate=true] modularous:migrate
```

After generation, runs `composer run-script pint modules/{module}` to auto-format the new files.

## `generateResources()`

| Resource | Command | Condition |
|----------|---------|-----------|
| Admin controller | `modularous:make:controller` | `paths.generator.route-controller` enabled |
| API controller | `modularous:make:controller:api` | `paths.generator.route-controller-api` enabled |
| Front controller | `modularous:make:controller:front` | `paths.generator.route-controller-front` enabled |
| Model | `modularous:make:model` | Always |
| Main migration | `modularous:make:migration` | No custom model — creates `create_{table}_table` |
| Add-columns migration | `modularous:make:migration` | Custom model present — creates `add_{table}_table` |
| Extra pivot migrations | `modularous:make:migration` | `belongsToMany` relationships in schema |
| Extra morph migrations | `modularous:make:migration` | `morphedByMany` relationships in schema |
| Repository | `modularous:make:repository` | `paths.generator.repository` enabled |
| Form request | `modularous:make:request` | `paths.generator.route-request` enabled |
| API resource | `module:make-resource` | `paths.generator.route-resource` enabled |
| Service provider | `module:make-provider` | Interactive prompt (or unit test mode) |
| Middleware | `module:make-middleware` | Interactive prompt (or unit test mode) |

## `updateConfigFile()`

Builds the route entry and writes it to the module's `Config/config.php`:

```php
$route_array = [
    'name'             => 'Post',
    'headline'         => 'Posts',
    'url'              => 'posts',
    'route_name'       => 'post',
    'icon'             => '$submodule',
    'title_column_key' => 'title',  // first 'name' or 'title' header, else first column
    'table_options'    => [
        'createOnModal' => true,
        'editOnModal'   => true,
        'isRowEditing'  => false,
        'rowActionsType'=> 'inline',
    ],
    'headers' => [...],   // from SchemaParser::getHeaderFormats()
    'inputs'  => [...],   // from SchemaParser::getInputFormats()
];
```

If the config file already exists, uses `add_route_to_config()`. Otherwise writes the full config via `php_array_file_content()`.

## `fixConfigFile()`

Called when `$fix = true`. Reads the existing config and merges the generated route array on top without overwriting manually set values. Preserves the existing `name`, `headline`, `icon`, `table_options`, `headers`, and `inputs` if already present.

## `addLanguageVariable()`

Adds a translation entry to the `modules` group for every installed locale:

```
Key:   {module_snake}.{route_snake}.name
Value: "{Headline} | {Plural} | {n} {Plural}"
```

Example for a route named `Post` in module `Blog`:
```
blog.post.name = "Post | Posts | {n} Posts"
```

## `createRoutePermissions()`

If `Modules\SystemUser\Repositories\PermissionRepository` exists, seeds the following Spatie permissions (guard = Modularous auth guard):

| Permission suffix | Ability |
|------------------|---------|
| `_create` | Create records |
| `_view` | View/list records |
| `_edit` | Edit records |
| `_delete` | Soft-delete |
| `_force-delete` | Permanent delete |
| `_restore` | Restore soft-deleted |
| `_duplicate` | Duplicate a record |
| `_reorder` | Change sort order |
| `_bulk` | Bulk operations |
| `_bulk-delete` | Bulk soft-delete |
| `_bulk-force-delete` | Bulk permanent delete |
| `_bulk-restore` | Bulk restore |

## `generateExtraMigrations()`

Scans `$relationships` for pivot-table relationships and generates dedicated migration files:

| Relationship type | Migration name |
|------------------|----------------|
| `belongsToMany` | `create_{source}_{target}_table` |
| `morphedByMany` | `create_{morph_pivot_name}_table` |

## Schema Format

```php
// $schema — column definitions
$generator->setSchema('title:string,body:text,status:enum("draft","published")');

// $rules — validation rules
$generator->setRules('title=required|min:3|unique:posts,body=required');

// $relationships — pipe-separated
$generator->setRelationships('tags:belongsToMany|author:belongsTo');
```
