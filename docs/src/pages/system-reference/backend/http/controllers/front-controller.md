---
sidebarPos: 12
sidebarTitle: FrontController
---

# FrontController

**File**: `src/Http/Controllers/FrontController.php`  
**Namespace**: `Unusualify\Modularity\Http\Controllers`  
**Extends**: `CoreController`

Abstract base controller for public-facing (non-authenticated) frontend routes within a Modularous module.

Inherits all module discovery, repository, and route utilities from `CoreController` but does **not** apply the authentication or permission middleware that `PanelController` adds.

## Usage

Generate a front controller for a module by extending this class directly:

```php
namespace Modules\Blog\Http\Controllers;

use Unusualify\Modularity\Http\Controllers\FrontController;

class PostController extends FrontController
{
    public function index()
    {
        $items = $this->repository->published()->paginate();

        return view('blog::posts.index', compact('items'));
    }
}
```

## Related

- [CoreController](./core-controller) — base class providing `$repository`, `$config`, and module context
- [BaseController](./base-controller) — authenticated admin-panel variant
