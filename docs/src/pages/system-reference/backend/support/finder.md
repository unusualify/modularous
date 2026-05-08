---
sidebarPos: 6
sidebarTitle: Finder
---

# Finder

`Unusualify\Modularity\Support\Finder`

Resolves Eloquent model and repository FQCN by table name, route name, or trait. Searches all enabled modules as well as the host application's `app/Models` and `app/Repositories` directories. Also accessible via the `UFinder` facade.

## Methods

### `getModel(string $table): string|false`

Find the first model class whose `getTable()` returns `$table`. Searches module `Entities/` directories first, then `app/Models/`.

```php
$class = UFinder::getModel('blog_posts');
// => 'Modules\Blog\Entities\Post'
```

### `getRouteModel(string $routeName, bool $asClass = false): string|object|false`

Resolve the model whose short class name matches the StudlyCase of `$routeName`.

```php
$class = UFinder::getRouteModel('blog-posts');
// => 'Modules\Blog\Entities\Post'

$instance = UFinder::getRouteModel('blog-posts', asClass: true);
// => Modules\Blog\Entities\Post instance
```

### `getRepository(string $table): string|false`

Find the repository class whose model resolves to `$table`.

```php
$repoClass = UFinder::getRepository('blog_posts');
// => 'Modules\Blog\Repositories\PostRepository'
```

### `getRouteRepository(string $routeName, bool $asClass = false): string|object|false`

Resolve a repository by the StudlyCase of the route name (expects `{Name}Repository` naming convention).

### `getPossibleModels(string $routeName): array`

Return all model FQCNs whose short name matches `$routeName` — useful when multiple modules define a model with the same name.

### `getModelsWithTrait(string $trait): array`

Return all Eloquent model FQCNs in the project that use the given trait.

```php
$models = UFinder::getModelsWithTrait(\Unusualify\Modularity\Traits\HasPayment::class);
```

### `getAllModels(): Collection`

Scan the composer classmap and return all user-defined, concrete, non-abstract Eloquent models as a Collection of FQCNs.

### `getClasses(string $path): array`

Use `composer/class-map-generator` to list all classes defined under the given directory.

## Facade

```php
use Unusualify\Modularity\Facades\UFinder;

$model = UFinder::getRouteModel('products');
```

## Related

- [Generators](/system-reference/backend/generators/overview) — use `Finder` internally to resolve models during scaffolding
