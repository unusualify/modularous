---
sidebarPos: 6
sidebarTitle: HasUuid
---

# HasUuid

**Namespace**: `Unusualify\Modularity\Entities\Traits\HasUuid`

Replaces the auto-increment primary key with an ordered UUID string. Uses `Str::orderedUuid()` so UUIDs sort chronologically in the database.

---

## Boot Behavior

| Event | Action |
|-------|--------|
| `creating` | Generates and sets `Str::orderedUuid()` on the UUID column if not already set |

---

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `getUuidColumn` | `(): string` (static) | Returns the UUID column name (default: `'id'`, override via `$uuidColumn`) |
| `getIncrementing` | `(): bool` | Returns `false` — primary key does not auto-increment |
| `getKeyType` | `(): string` | Returns `'string'` — primary key is a string |

---

## Configuration

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$uuidColumn` | `string` | `'id'` | Column name that stores the UUID |

```php
// Use a non-PK UUID column
public static string $uuidColumn = 'uuid';
```

---

## Usage

```php
use Unusualify\Modularity\Entities\Traits\HasUuid;

class Session extends Model
{
    use HasUuid;
}

$session = Session::create(['user_id' => 1]);
$session->id;   // e.g. "018f1e2a-3bcd-7000-8000-000000000000"

// Finding by UUID
Session::find('018f1e2a-3bcd-7000-8000-000000000000');
```

::: tip Migration note
Your migration must declare the primary key as `uuid` or `char(36)`:
```php
$table->uuid('id')->primary();
```
:::
