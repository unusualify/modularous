---
sidebarPos: 5
sidebarTitle: Dates
---

# Dates

**Namespace**: `Unusualify\Modularous\Repositories\Logic\Dates`

Normalises date fields to `Y-m-d H:i:s` format before they are saved to the database. Works with Carbon for robust parsing of whatever date string the frontend sends.

## Lifecycle Hooks

| Hook | Signature | Description |
|------|-----------|-------------|
| `prepareFieldsBeforeCreateDates` | `($fields): array` | Delegates to `prepareFieldsBeforeSaveDates(null, $fields)` |
| `prepareFieldsBeforeSaveDates` | `($object, $fields): array` | Iterates `$model->getDates()` and normalises any matching field present in `$fields`. Empty values are set to `null`. |

## Normalisation

```
Input:  fields['published_at'] = '2024-06-15T14:30:00.000Z'   (ISO 8601 from JS)
Output: fields['published_at'] = '2024-06-15 14:30:00'         (MySQL format)

Input:  fields['expires_at'] = ''
Output: fields['expires_at'] = null
```

If `Carbon::parse()` throws (unparseable value), the field is set to `null` rather than propagating the exception.

## Usage

Dates normalisation is automatic for any column returned by the model's `getDates()` method. No additional configuration is required — add date columns to your model's `$casts` or `$dates` array:

```php
class Post extends Model
{
    protected $casts = [
        'published_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];
}
```
