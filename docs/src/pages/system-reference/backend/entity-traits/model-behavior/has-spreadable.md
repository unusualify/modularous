---
sidebarPos: 4
sidebarTitle: HasSpreadable
---

# HasSpreadable

**Namespace**: `Unusualify\Modularous\Entities\Traits\HasSpreadable`

Stores arbitrary non-column attributes in a JSON `Spread` morph record, surfacing them as native model properties via `__get` / `__call` magic. Useful when you need dynamic, schema-free attributes without adding database columns.

---

## Boot Behavior

| Event | Action |
|-------|--------|
| `saving` (new record) | Captures `spread_payload` attribute into `$spreadablePayload` for creation |
| `saving` (existing record) | Writes the `spread_payload` directly to the `Spread` record; removes the virtual attribute |
| `created` | Creates the `Spread` morph record with `$spreadablePayload` |
| `retrieved` | Loads `Spread` content; registers each JSON key as a virtual attribute via `$spreadableMutatorAttributes` |
| `saved` | Touches the model's `updated_at` if the Spread changed but the model itself didn't |

---

## Relationship

```php
public function spreadable(): MorphOne   // → Spread model
```

---

## Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$spreadableSavingKey` | `string` | `'spread_payload'` | The virtual fillable key that triggers a Spread write on save |
| `$spreadableClass` | `string\|null` | `null` | Optional proxy class (morph owner override) |

---

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `getSpreadableSavingKey` | `(): string` (static, final) | Returns the virtual attribute name (default `'spread_payload'`) |
| `getSpreadableKeys` | `(): array` | Returns the list of JSON keys currently spread onto the model |
| `hasSpreadable` | `(): bool` | Returns `true` if a `Spread` record exists |

---

## Global Scopes

Registers `spreadable_exists` via `addGlobalScopesHasSpreadable()`:
- Adds `withExists('spreadable')` to all queries so `$model->spreadable_exists` avoids a lazy-load.

---

## Usage

```php
use Unusualify\Modularous\Entities\Traits\HasSpreadable;

class Product extends Model
{
    use HasSpreadable;
}

// Writing spread attributes
$product->spread_payload = ['meta_title' => 'My Product', 'meta_description' => 'Best product ever'];
$product->save();

// Reading spread attributes (automatically available after retrieval)
$product->meta_title;         // 'My Product'
$product->meta_description;

// Checking keys
$product->getSpreadableKeys(); // ['meta_title', 'meta_description']
```

::: tip Custom saving key
Override the virtual attribute name in your model:
```php
public static string $spreadableSavingKey = '_extra';
```
Then use `$product->_extra = [...]; $product->save();`
:::
