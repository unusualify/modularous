---
sidebarPos: 2
sidebarTitle: HasPresenter
---

# HasPresenter

**Namespace**: `Unusualify\Modularous\Entities\Traits\HasPresenter`

Lightweight presenter pattern — wraps the model in a presenter class for display logic. Supports a main presenter and an admin-specific presenter. Presenter instances are cached on the model instance.

---

## Properties

| Property | Type | Description |
|----------|------|-------------|
| `$presenter` | `string` | FQN of the presenter class (used by `present()`) |
| `$presenterAdmin` | `string` | FQN of the admin presenter class (used by `presentAdmin()`) |

---

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `present` | `(string $presenter = 'presenter'): object` | Returns a presenter instance (defaults to `$this->presenter`); throws if not set or class not found |
| `presentAdmin` | `(): object` | Returns the admin presenter (`$this->presenterAdmin`) |
| `setPresenter` | `(string $presenter, string $presenterProperty = 'presenter'): static` | Sets the presenter class at runtime (no-op if already set) |
| `setPresenterAdmin` | `(string $presenter): static` | Sets the admin presenter class at runtime |

---

## Usage

```php
use Unusualify\Modularous\Entities\Traits\HasPresenter;

class Article extends Model
{
    use HasPresenter;

    protected $presenter      = ArticlePresenter::class;
    protected $presenterAdmin = ArticleAdminPresenter::class;
}

// Using the presenter
$article->present()->title();
$article->present()->formattedDate();

// Using the admin presenter
$article->presentAdmin()->statusBadge();

// Setting at runtime (e.g., in a transformer)
$article->setPresenter(ArticleApiPresenter::class);
$article->present()->toArray();
```

::: tip Presenter structure
A presenter typically extends a base `Presenter` class and receives `$this->entity` in the constructor:
```php
class ArticlePresenter
{
    public function __construct(protected Article $entity) {}

    public function title(): string
    {
        return $this->entity->title ?? 'Untitled';
    }
}
```
:::
