---
sidebarPos: 6
sidebarTitle: ModuleServiceProvider
---

# ModuleServiceProvider

**Class**: `Unusualify\Modularous\Providers\ModuleServiceProvider`  
**Source**: `src/Providers/ModuleServiceProvider.php`  
**Extends**: [`ServiceProvider`](./service-provider)  
**Implements**: `Illuminate\Contracts\Support\DeferrableProvider`

Bootstraps every enabled nwidart module. Iterates `Modularous::allEnabled()` and registers each module's providers, config, commands, migrations, views, Blade components, and translations.

## `bootModules()`

For each enabled module, the following steps run in order:

### 1. Middleware aliases

Calls `$module->createMiddlewareAliases()` to register any middleware defined in the module's configuration.

### 2. Service providers

Scans the module's provider directory for `*ServiceProvider.php` files and registers each one via `$this->app->register()`.

### 3. Config

Calls `$module->loadConfig()` to merge the module's config files.

### 4. Commands

Calls `$module->loadCommands()` to register the module's artisan commands.

### 5. Migrations

```php
$this->loadMigrationsFrom($module->getDirectoryPath($migration_folder));
```

### 6. Views

Loads views from the publishable override path first, then the module source path, under the module's snake-case namespace:

```php
$this->loadViewsFrom(
    array_merge($this->getPublishableViewPaths($module->getSnakeName()), [$sourcePath]),
    $module->getSnakeName()
);
```

### 7. Blade components

```php
Blade::componentNamespace($namespace, snakeCase($module_name));
```

### 8. Translations

Checks `lang/modules/{snake_name}` in the application root first (override). Falls back to the module's own `Resources/lang` directory.

## Config paths

The provider reads paths from nwidart's `GenerateConfigReader` so the folder layout is driven by the module generator config:

| Key | Reader | Default path |
|-----|--------|-------------|
| Migrations | `migration` | `Database/Migrations` |
| Config | `config` | `Config` |
| Providers | `provider` | `Providers` |
| Views | `views` | `Resources/views` |
| Lang | `lang` | `Resources/lang` |
| Blade components | `component-class` | `View/Components` |

## DeferrableProvider

Implementing `DeferrableProvider` means Laravel only resolves this provider when one of its provided bindings is actually needed. `register()` is empty — all work happens in `boot()`.
