---
outline: deep
sidebarPos: 4
---

# Relationships

All of Modularous relationships rely on [Laravel Eloquent Relationships](https://laravel.com/docs/eloquent-relationships). We suppose that you know these relationship concepts. At now, we provide many of these as following:

- hasOne
- belongsTo
- hasMany
- belongsToMany
- hasOneThrough
- hasManyThrough
- morphTo
- morphToMany
- morphMany
- morphedByMany

## Get Started
We'll be explaining how to use these relationships on making and creating sources. We have some critical concepts for maintainability of system infrastructure. You should think each creation as a step or stage. Every stage interests both previous and next stage. You must follow instructions in the way we pointed while creating the system skeleton.

Modularous System has multiple relationship constructor mechanism. While making model and creating a module route, you can define relationships. But the **make:route** command get relationships schema and convert it the way adapted **make:model** _--relationships_. **|** delimeter can be considered array explode operator. For example, basically --relationships="name1:arg1|name2:arg2" option points stuff as following
``` php
  [
    name1 => [
      arg1
    ],
    name2 => [
      arg2
    ]
  ]
```

## Model Relationships

Model Relationships parameter add only methods to parent model, so it matters method names and parameters for special cases. 

<!-- "Model Relationships" => "belongsToMany:PackageFeature,position:integer,active:string|belongsToMany:PackageLanguage" -->
### Synopsis
```bash
php artisan modularity:make:model <modelName> <moduleName> [--relationships=<MODELRELATIONSHIPS>] [options]
```

```bash
--relationships=<MODELRELATIONSHIPS> (optional)
```
   Comma-separated list of relationships. Each relationship is defined as:

```js
<relationship_type>:<model_name>[,<field_name>:<field_type>]
```

   - `<relationship_type>`: The type of relationship (currently limited to "belongsToMany").
   - `<model_name>`: The name of the model involved in the relationship (e.g., PackageFeature, PackageLanguage).
   - `[,<field_name>:<field_type>]`: Optional field definitions, zero or more allowed.
       - `<field_name>`: The name of the field in the model (optional).
       - `<field_type>`: The data type of the field (optional, if specified).

   **Note:** Currently, this option only supports "belongsToMany" relationships. 
           Field definitions are optional but can be included for each relationship.

### Examples

Here are two valid examples of the `--relationships` argument:

1. Simple relationship with model name only:

```ini
--relationships="belongsToMany:Feature"
```

2. Relationship with a field definition:

```ini
--relationships="belongsToMany:PackageFeature,position:integer"
```

**Future Considerations:**

   Future versions of this utility may allow more complex relationship definitions with additional options. This help message provides a foundation for future expansion.

          


## Route Relationships

Route relationships parameter is more complex than model relationships, as it does what model relationships do and also handles other necessary system infrastructure elements. Pivot model and migration generating, chaining methods for sometimes pivot table column fields, reverse relationships to related models. The syntax is more similar to --schema than --relationships option of the model command.

<!-- "Route Relationships" => "package_feature:belongsToMany,position:integer:unsigned:index,active:string:default(true)|package_language:belongsToMany" -->
### Synopsis
  <!-- package_feature:belongsToMany,position:integer:unsigned:index,active:string:default(true)|package_language:belongsToMany -->
  <!-- [--relationships=[{routeName|columnName}:{relationshipCamelName|migrationMethodName}:{migrationChainMethod[:...]}[,...]][|...]] -->
```bash
php artisan modularity:make:route <moduleName> <routeName> [--relationships=<ROUTERELATIONSHIPS>] [options]
```

```bash
--relationships=<ROUTERELATIONSHIPS> (optional)
```
Comma-separated list of relationships. Each relationship is defined as:

```js
<model_name>:<relationship_type>,<field_name>:<field_type>[:<modifiers>]
```

- `<model_name>`: The name of the model involved in the relationship.
- `<relationship_type>`: The type of relationship (e.g., belongsToMany).
- `<field_name>`: The name of the field in the model.
- `<field_type>`: The data type of the field (e.g., integer, string).
- `[:<modifiers>]`: Optional modifiers for the field (e.g., unsigned, index, default(value)).

You can define multiple relationships separated by a pipe character (|).

### Examples

Here are two valid examples of the `--relationships` argument:

1. Simple relationship with model name only:

```ini
--relationships="PackageLanguage:morphToMany"
```

2. Relationship with a field definition:

```ini
--relationships="PackageFeature:belongsToMany,position:integer:unsigned:index,active:string:default(true)|PackageLanguage:morphToMany"
```

---

## Runtime Usage

Once generated, relationships behave like any Laravel Eloquent relationship. The examples below show the most common access patterns.

### belongsToMany with pivot fields

```php
$package = Package::find(1);

// Attach with pivot fields
$package->features()->attach($featureId, [
    'position' => 3,
    'active' => true,
]);

// Sync (replace the whole set)
$package->features()->sync([
    1 => ['position' => 0, 'active' => true],
    2 => ['position' => 1, 'active' => false],
]);

// Read pivot fields
foreach ($package->features as $feature) {
    $feature->pivot->position;
    $feature->pivot->active;
}

// Ordered by pivot field
$ordered = $package->features()->orderBy('pivot_position')->get();
```

### morphToMany / morphMany

```php
// morphToMany — polymorphic many-to-many
$post->tags()->attach($tagId);
$tag->posts;  // reverse side

// morphMany — polymorphic one-to-many
$user->notifications;
$notification->notifiable;  // back to owner
```

### hasManyThrough

```php
// Country → Post (through User)
$country->posts;  // all posts by users in this country
```

---

## Real-World Examples

### 1. Simple many-to-many (Package ↔ Feature)

```bash
php artisan modularity:make:route Billing packages \
  --relationships="Feature:belongsToMany"
```

Creates: pivot table `package_feature`, `features()` on Package, `packages()` on Feature.

```php
$package->features;      // Collection<Feature>
$feature->packages;      // Collection<Package>
```

### 2. Ordered many-to-many with pivot fields

```bash
php artisan modularity:make:route Billing packages \
  --relationships="Feature:belongsToMany,position:integer:unsigned:index,active:string:default(true)"
```

Generates pivot `package_feature` with `position` (indexed) and `active` columns.

```php
$package->features()
    ->wherePivot('active', true)
    ->orderBy('pivot_position')
    ->get();
```

### 3. Polymorphic tagging

```bash
php artisan modularity:make:route Content tags \
  --relationships="Taggable:morphToMany"
```

Any model can then use the polymorphic side:

```php
class Post extends Model
{
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}

$post->tags()->attach($tagIds);
```

### 4. Model-only relation (no pivot generation)

```bash
php artisan modularity:make:model Comment Blog \
  --relationships="belongsToMany:Post"
```

Adds only the method on the Comment model; does **not** generate a pivot migration. Use when the pivot already exists or is managed elsewhere.

---

## Config-Driven Relationships

Modularous route configs can declare relationships alongside inputs. The hydrate layer picks them up when building form schema:

```php
// Route config
[
    'name' => 'packages',
    'inputs' => [
        ['type' => 'text', 'name' => 'title'],
        [
            'type' => 'input-relationships',
            'name' => 'features',
            'relation' => 'belongsToMany',
            'related' => Feature::class,
            'pivot' => ['position' => 'integer'],
        ],
    ],
]
```

See [input-relationships](/guide/form-inputs/input-relationships) for the full input component and [Relationships entity traits](/system-reference/backend/overview) for how `HasAssociations`, `HasChildren`, `HasRelatedItems` expose typed relations.

---

## Common Pitfalls

| Issue | Fix |
|-------|-----|
| Pivot field not readable | Call `->withPivot('field')` on the relation or define pivot fields on generation |
| Ordering broken on `belongsToMany` | Use `orderBy('pivot_<field>')`, not `orderBy('<field>')` |
| Reverse relation missing | Use `make:route` (generates both sides), not `make:model` (adds one method only) |
| morphToMany table name wrong | Singular relation name (e.g. `taggable`), matches the `{name}_type` / `{name}_id` columns |

## Related

- [Module Features](/guide/module-features/overview) — feature patterns built on relationships
- [Entities](/system-reference/backend/overview) — model hierarchy and relationship traits
- [input-relationships](/guide/form-inputs/input-relationships) — the form input that renders a relation picker
          
