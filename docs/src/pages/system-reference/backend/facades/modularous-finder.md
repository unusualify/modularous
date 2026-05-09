---
sidebarPos: 10
sidebarTitle: ModularousFinder
---

# ModularousFinder

**Facade**: `Unusualify\Modularous\Facades\ModularousFinder`  
**Accessor**: `Unusualify\Modularous\Support\Finder::class`  
**Underlying**: `Unusualify\Modularous\Support\Finder`

Resolves model class names, repository class names, and related metadata from route names or database table names. Used internally by the cache system, middleware, and console commands to dynamically discover module components.

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `getModel` | `(string $table): string\|false` | Returns the model FQCN for a given table name |
| `getRouteModel` | `(string $routeName, bool $asClass = false): string\|false` | Returns the model for a route name |
| `getRepository` | `(string $table): string\|false` | Returns the repository FQCN for a given table name |
| `getRouteRepository` | `(string $routeName, bool $asClass = false): string\|false` | Returns the repository for a route name |
| `getPossibleModels` | `(string $routeName): array` | Returns all candidate model classes for a route |
| `getClasses` | `(string $path): array` | Returns all PHP class names found under a directory path |
| `getAllModels` | `(): Collection` | Returns all registered model classes across all modules |

## Usage

```php
use Unusualify\Modularous\Facades\ModularousFinder;

// Resolve a model from a table name
$modelClass = ModularousFinder::getModel('blog_posts');
// → 'Modules\Blog\Entities\Post'

// Resolve a repository from a route name
$repoClass = ModularousFinder::getRouteRepository('blog.posts');
// → 'Modules\Blog\Repositories\PostRepository'

// Get all models across all modules
$allModels = ModularousFinder::getAllModels();
```

## Notes

- `UFinder` is a deprecated alias for this facade. Use `ModularousFinder` in new code.
- Resolves using a combination of module config, naming conventions, and class-map scanning.
