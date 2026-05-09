---
sidebarPos: 11
sidebarTitle: Connector
---

# Connector

**File**: `src/Services/Connector.php`

The `Connector` class parses a **connector string** — a compact DSL that describes where a form input should fetch its data from. It is the backbone of all `connector:` keys in input field configurations, used by hydrates to resolve remote endpoints and repository calls.

## Connector String Syntax

```
ModuleName|RouteName^TargetType->method?arg1=value1&arg2=value2
```

| Segment | Separator | Meaning |
|---------|-----------|---------|
| `ModuleName` | `\|` | The registered Modularous module name |
| `RouteName` | `^` | The route key within that module (defaults to module name if omitted) |
| `TargetType` | `->` | `endpoint` / `url` / `uri` for a URL, or a class key such as `repository` |
| `method` | `?` | Method to call on the target (e.g. `list`) |
| `arg=value` | `&` | Named arguments passed to the method |
| `[a,b,c]` | — | Array argument value |
| `{key:val}` | — | Object/map argument value |

### Target types

| Type | Resolves to |
|------|-------------|
| `endpoint` / `url` / `uri` | A URL string — calls `getRouteActionUrl()` on the module. Sets `endpoint` in the result. |
| `repository` | The module's repository instance — subsequent method calls chain on it. Sets `items` in the result. |

## Examples

```php
// Returns the index URL of the PackageRegion route inside the Package module
'connector' => 'Package|PackageRegion^endpoint->index'

// Calls repository->list() with named scopes and appends
'connector' => 'Package|PackageRegion^repository->list?scopes=hasVendablePackage&appends=[number_of_countries,number_of_package_languages]'

// Array argument syntax
'connector' => 'Product^repository->list?appends=[price,currency]'

// Object argument syntax
'connector' => 'Product^repository->list?filters={status:active,type:digital}'
```

## Key Methods

| Method | Description |
|--------|-------------|
| `run(&$item, $setKey)` | Execute the connector and write the result into `$item[$setKey]` |
| `getModule()` | Return the resolved `Module` instance |
| `getModuleName()` | Return the module name string |
| `getRouteName()` | Return the resolved route name string |
| `getTarget()` | Return the resolved target object (module or repository instance) |
| `getTargetTypeKey()` | Return the target type string (`endpoint`, `repository`, etc.) |
| `isLinkTarget()` | Returns `true` when the target type is `endpoint`/`url`/`uri` |
| `pushEvent($event)` | Append an extra method call to the execution chain |
| `unshiftEvent($event)` | Prepend an extra method call to the execution chain |
| `pushEvents($events)` | Append multiple events at once |
| `unshiftEvents($events)` | Prepend multiple events at once |
| `updateEventParameters($name, $params)` | Merge additional arguments into a named event |
| `getEvents()` | Return the full event chain array |
| `getRepository($asClass)` | Resolve the module's repository for this route |
| `getModel($asClass)` | Resolve the module's model for this route |

## Usage in Hydrates

Hydrates that accept a `connector` config key (e.g. `SelectHydrate`, `RelationshipsHydrate`) instantiate `Connector` and call `run()` to populate the input's options or endpoint:

```php
$connector = new Connector($config['connector']);
$connector->run($fieldSchema, 'endpoint');
// $fieldSchema['endpoint'] now contains the resolved URL or item list
```

## Usage in Navigation

`ModularousNavigation` uses `Connector` to resolve badge counts on sidebar menu items:

```php
// In a module's menu config
'badge' => null,
'connector' => 'Orders^repository->count?scopes=pending',
```

The connector resolves the count and injects it as `badge` into the menu item array.

## Error Handling

The constructor throws exceptions for invalid connector strings:

| Exception | Cause |
|-----------|-------|
| `ModuleNotFoundException::moduleMissing` | Module name is empty |
| `ModuleNotFoundException::moduleNotFound` | Module is not registered |
| `ModuleNotFoundException::routeNotFound` | Route key does not exist in the module |
| `\Exception` | Class resolved for target type does not exist |
