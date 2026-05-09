---
outline: deep
sidebarPos: 3
---

# File Storage with Filepond

`Modularous` provides two different file storage functionality, with file library method and filepond. These two systems, differentiate over `file - fileable object` relationship and input component used over forms. This documentation will only cover the filepond mechanism.

## Storage Mechanism

Filepond storage mechanism is designed based on [FilePond Vue Component Docs](https://pqina.nl/filepond/docs/api/server/), which requires and serves `temporary asset` processing. For an example, let's say project have system users and users can upload their avatar(s). 
* When a file is uploaded through the FilePond interface, it is sent to our backend via a secure API endpoint. 
* Then, our `FilePondManager` processes the file upload request, performs necessary validations and stores the file in temporary file storage path and file data in `temporary file table`.
* During this stage, the file is cached to enhance performance and allow for any further processing or validation checks. And it is ready for permanent storage
* Once the associated model form is confirmed or saved, the file is then moved from the temporary cache to its permanent storage location and a file object will be created on the permanent asset table.

  
::: info

This approach ensures efficient file handling, reducing the load on the system and improving the overall user experience. Our architecture ensures high reliability and scalability, capable of managing multiple concurrent uploads seamlessly.

:::

Regarding the object relations, `modularous's filepond` offers `one to many polymorphic` relation between assetable objects and assets. Database structure can be observed below for user-assets mechanism.

<img src="https://i.ibb.co/WvdQsCh/Screenshot-2024-07-23-at-11-53-36.png" alt="filepond_db_relations" border="0" />

::: tip

In order to implement and use filepond on file storage, see [Files and Media](/guide/module-features/files-and-media) for the Filepond triple pattern.

:::

## Quick Setup

Three steps wire up Filepond for any entity:

### 1. Model — `HasFileponds` trait

```php
use Unusualify\Modularous\Entities\Traits\HasFileponds;

class Ticket extends Model
{
    use HasFileponds;
}
```

Adds a `morphMany(Filepond::class, 'filepondable')` relation and accessors: `fileponds()`, `getFileponds()`, `hasFilepond()`.

### 2. Repository — `FilepondsTrait`

```php
use Unusualify\Modularous\Repositories\Traits\FilepondsTrait;

class TicketRepository extends Repository
{
    use FilepondsTrait;
}
```

Handles moving files from the temporary table to permanent storage on save, and reverting on rollback.

### 3. Route config — `filepond` input

```php
'inputs' => [
    [
        'type' => 'filepond',
        'name' => 'attachments',
        'max' => 5,
        'acceptedExtensions' => ['pdf', 'doc', 'docx', 'png', 'jpg'],
    ],
]
```

That's it. The hydrate (`FilepondHydrate`) translates this to an `input-filepond` component with the correct `process`, `revert`, and `load` endpoints.

---

## Runtime Examples

### Accessing files on a model

```php
$ticket = Ticket::find(1);

// All fileponds regardless of role
$ticket->fileponds;

// Fileponds for a specific role/locale
$ticket->getFileponds('attachments');

// Boolean check
if ($ticket->hasFilepond('attachments')) {
    // ...
}
```

### Iterating in Blade / Vue

```blade
@foreach ($ticket->getFileponds('attachments') as $file)
    <a href="{{ $file->url }}" download>{{ $file->name }}</a>
@endforeach
```

### Avatar upload (single file)

```php
'inputs' => [
    [
        'type' => 'filepond',
        'name' => 'avatar',
        'max' => 1,
        'acceptedExtensions' => ['png', 'jpg', 'jpeg', 'webp'],
    ],
]
```

Use `input-filepond-avatar` if you want the round avatar UI preset (see [Form Inputs](/guide/form-inputs/overview)).

### Attachments with size limit

```php
[
    'type' => 'filepond',
    'name' => 'documents',
    'max' => 10,
    'maxFileSize' => '5MB',
    'acceptedExtensions' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
]
```

---

## Filepond vs Files vs Images

| Use Case | Pick |
|----------|------|
| User avatar, ticket attachments, any direct 1:N upload | **Filepond** (`HasFileponds`) |
| Shared document library reused across records | **Files** (`HasFiles`) |
| Images with cropping, role/locale variants, transformations | **Media** (`HasImages`) |

Filepond is the lightest option — no library, no pivot metadata beyond the polymorphic link. See [Files and Media](/guide/module-features/files-and-media) for the full comparison.

## Related

- [Files and Media](/guide/module-features/files-and-media) — the triple pattern in full
- [FilepondHydrate](/system-reference/backend/overview) — schema transformation
- [Filepond entity](/system-reference/backend/overview) — model, columns, relationships
