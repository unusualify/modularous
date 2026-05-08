---
sidebarPos: 6
sidebarTitle: connector
---

# connector

**File**: `src/Helpers/connector.php`

Entrypoint helpers for the Modularous **Connector** system — the mechanism that links form inputs to repository actions across modules.

## Functions

### `init_connector`

```php
init_connector(string $moduleName, string $routeName, array $config = []): mixed
```

Main entrypoint. Resolves the module and route, then delegates to `find_target` and `exec_target`. Returns the result of the connector action.

---

### `find_module_and_route`

```php
find_module_and_route(string $moduleName, string $routeName): array
```

Looks up the module via `Modularity::find()` and returns `[$module, $route]`. Throws if either cannot be resolved.

---

### `find_module_route_names`

```php
find_module_route_names(string $moduleName): array
```

Returns all route names registered in a module. Uses `Modularity::find($moduleName)->getRouteNames()`.

---

### `get_connector_event`

```php
get_connector_event(string $moduleName, string $routeName, string $event): mixed
```

Retrieves the connector event configuration for a specific module/route/event combination from the module's route config.

---

### `change_connector_event`

```php
change_connector_event(string $moduleName, string $routeName, string $event, mixed $value): void
```

Mutates the connector event value in the module's route config. Used during form schema build to wire input events.

---

### `find_target`

```php
find_target(array $config): array
```

Parses the connector `$config` to determine the target type (`uri` or `repository`) and returns a normalised descriptor array including the resolved endpoint URL or repository class name.

---

### `exec_target`

```php
exec_target(array $target, string $method, array $params = []): mixed
```

Executes the resolved target. For `repository` targets it calls `App::make($target['repository'])->{$method}(...$params)`. For `uri` targets it returns the URL for the frontend to call.

---

### `get_item_columns`

```php
get_item_columns(string $moduleName, string $routeName): array
```

Returns the column configuration for the items list of a connector-backed input — resolves the module's repository, calls `getColumns()`, and formats them for the frontend select/combobox input.
