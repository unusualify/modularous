<?php

namespace Unusualify\Modularous\Entities\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;
use Oobook\Database\Eloquent\Concerns\ManageEloquent;
use Unusualify\Modularous\Entities\Spread;

trait HasSpreadable
{
    use ManageEloquent;

    protected $spreadablePayload;

    protected $spreadableMutatorMethods = [];

    protected $spreadableMutatorAttributes = [];

    protected $spreadableKeys = [];

    protected $spreadableIsUpdated = false;

    protected $spreadablePending = false;

    public static function bootHasSpreadable()
    {
        // TODO: Keep the old spreadable data from model and remove attributes based on that don't remove all column fields
        self::saving(static function (Model $model) {

            // Store the spread data before cleaning
            if (! $model->exists) { // fill if creating a new record
                // Set property to preserve data through events
                $model->spreadablePayload = $model->{$model->getSpreadableSavingKey()} ?: $model->prepareSpreadableJson();
            } elseif ($model->{$model->getSpreadableSavingKey()}) {
                if (! $model->spreadable) {
                    $spreadable = $model->spreadable()->create([
                        'content' => $model->{$model->getSpreadableSavingKey()},
                    ]);

                    $model->spreadableIsUpdated = true;
                } else {
                    // Handle existing spread updates
                    $spreadable = $model->spreadable;
                    $spreadable->update([
                        'content' => $model->{$model->getSpreadableSavingKey()},
                    ]);
                    if ($spreadable->wasChanged()) {
                        $model->spreadableIsUpdated = true;
                    }
                }
            }

            $model->offsetUnset($model->getSpreadableSavingKey());

            // $model->cleanSpreadableAttributes();
            // dd($model);
        });

        self::created(static function (Model $model) {
            $model->spreadable()->create([
                'content' => $model->spreadablePayload ?? [],
            ]);
            foreach ($model->spreadablePayload ?? [] as $key => $value) {
                if (! $model->isProtectedAttribute($key)) {
                    $model->append($key);
                    $model->spreadableMutatorMethods['get' . Str::studly($key) . 'Attribute'] = $value;
                    $model->spreadableMutatorAttributes[$key] = $value;
                    $model->spreadableMutatorAttributes[Str::camel($key)] = $value;
                }
            }

        });

        // Spread content is applied in batch by newCollection(), or lazily on first access
        // for models that never pass through a collection (e.g. cursor()).
        self::retrieved(static function (Model $model) {
            $exists = $model->getAttributes()['spreadable_exists'] ?? null;

            $model->spreadablePending = $exists === null || (bool) $exists;
        });

        self::saved(static function (Model $model) {
            if ($model->spreadableIsUpdated && ! $model->wasChanged()) {
                $model->touch();
            }
        });

    }

    public function initializeHasSpreadable()
    {
        $this->makeHidden(array_merge($this->hidden, ['spreadable']));

        $this->mergeFillable([$this->getSpreadableSavingKey()]);

        // $this->append($this->getSpreadableSavingKey());
    }

    public static function addGlobalScopesHasSpreadable()
    {
        return [
            'spreadable_exists' => [
                'scope' => function ($query) {
                    $query->withExists('spreadable');
                },
            ],
        ];
    }

    protected function getSpreadableClass(): Model
    {
        if (! property_exists(static::class, 'spreadableClass') || ! static::$spreadableClass || ! class_exists(static::$spreadableClass)) {
            return $this;
        }

        $class = new static::$spreadableClass;

        $class->setAttribute($this->getKeyName(), $this->getKey());
        $class->fill($this->getAttributes());
        $class->setRelations($this->getRelations());

        return $class;
    }

    /**
     * Pre-computed flag from withExists('spreadable') in the fetch query.
     * Avoids lazy load when checking if spreadable exists.
     */
    protected function spreadableExists(): Attribute
    {
        return Attribute::get(function (?int $value) {
            return $value !== null ? (bool) $value : $this->spreadable()->exists();
        });
    }

    /**
     * Check if spreadable exists without triggering a lazy load when
     * the model was fetched with withExists('spreadable') (via global scope).
     */
    protected function hasSpreadable(): bool
    {
        return $this->spreadable_exists ?? $this->spreadable()->exists();
    }

    // TODO: rename relation to spread as well
    public function spreadable(): MorphOne
    {
        $spreadableClass = $this->getSpreadableClass();

        return $spreadableClass->morphOne(
            Spread::class,
            'spreadable'
        );
    }

    protected function isProtectedAttribute(string $key): bool
    {
        return in_array($key, $this->getReservedKeys());
    }

    public function getReservedKeys(): array
    {
        return array_merge(
            $this->getTableColumns(),  // Using ManageEloquent's getTableColumns
            $this->definedRelations(), // Using ManageEloquent's definedRelations
            array_keys($this->getMutatedAttributes()),
            ['spreadable', '_spread']
        );
    }

    protected function prepareSpreadableJson(): array
    {
        $attributes = $this->getAttributes();
        $protectedKeys = array_merge(
            $this->getTableColumns(), // Using ManageEloquent's getTableColumns
            $this->definedRelations(), // Using ManageEloquent's definedRelations
            array_keys($this->getMutatedAttributes()),
            ['spreadable', $this->getSpreadableSavingKey()]
        );

        return array_diff_key(
            $attributes,
            array_flip($protectedKeys)
        );
    }

    protected function cleanSpreadableAttributes(): void
    {
        $columns = $this->getTableColumns(); // Using ManageEloquent's getTableColumns
        $attributes = $this->getAttributes();
        // TODO: Instead of removing any attribute remove the ones that you know that needs to be removed
        // Remove any attributes that aren't database columns

        // $this->spreadable->content ??= [];
        foreach ($attributes as $key => $value) {
            if (! in_array($key, $columns)) {
                unset($this->attributes[$key]);
            }
        }

    }

    public function getSpreadableKeys(): array
    {
        $this->resolvePendingSpreadable();

        return $this->spreadableKeys;
    }

    /**
     * @param array<int, Model> $models
     */
    public function newCollection(array $models = [])
    {
        $pending = array_values(array_filter(
            $models,
            fn ($model) => $model instanceof self && $model->spreadablePending && ! $model->relationLoaded('spreadable')
        ));

        if (count($pending) > 1) {
            (new EloquentCollection($pending))->load('spreadable');
        }

        foreach ($models as $model) {
            if ($model instanceof self) {
                $model->resolvePendingSpreadable();
            }
        }

        return parent::newCollection($models);
    }

    public function resolvePendingSpreadable(): void
    {
        if (! $this->spreadablePending) {
            return;
        }

        $this->spreadablePending = false;

        $jsonData = $this->spreadable?->content ?? [];
        $this->spreadableKeys = array_keys($jsonData);

        foreach ($jsonData as $key => $value) {
            if (! $this->isProtectedAttribute($key)) {
                $this->append($key);
                $this->spreadableMutatorMethods['get' . Str::studly($key) . 'Attribute'] = $value;
                $this->spreadableMutatorAttributes[$key] = $value;
                $this->spreadableMutatorAttributes[Str::camel($key)] = $value;
            }
        }
    }

    /**
     * Columns, relations and accessors can never come from spread content, so reading
     * them (e.g. from other retrieved hooks) must not force a per-model spread query.
     */
    public function mayBeSpreadableKey(string $key): bool
    {
        return $this->spreadablePending
            && ! array_key_exists($key, $this->attributes)
            && ! $this->relationLoaded($key)
            && ! method_exists($this, $key)
            && ! $this->hasGetMutator($key)
            && ! $this->hasAttributeMutator($key);
    }

    protected function getArrayableAppends()
    {
        $this->resolvePendingSpreadable();

        return parent::getArrayableAppends();
    }

    final public static function getSpreadableSavingKey()
    {
        return static::$spreadableSavingKey ?? 'spread_payload';
    }
}
