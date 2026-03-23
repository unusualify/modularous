<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Oobook\Database\Eloquent\Concerns\ManageEloquent;

trait HasSpreadable
{
    use ManageEloquent;

    protected $spreadablePayload;

    protected $spreadableMutatorMethods = [];

    protected $spreadableMutatorAttributes = [];

    protected $spreadableKeys = [];

    protected $spreadableIsUpdated = false;

    public static function bootHasSpreadable()
    {
        // TODO: Keep the old spreadable data from model and remove attributes based on that don't remove all column fields
        self::saving(static function (Model $model) {
            // Store the spread data before cleaning
            if (! $model->exists) {
                // Set property to preserve data through events
                $model->spreadablePayload = $model->{$model->getSpreadableSavingKey()} ?: $model->prepareSpreadableJson();
            } elseif ($model->{$model->getSpreadableSavingKey()}) {
                if (! $model->spreadable) {
                    $model->spreadable()->create([
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
            foreach ($model->spreadablePayload as $key => $value) {
                if (! $model->isProtectedAttribute($key)) {
                    $model->append($key);
                    $model->spreadableMutatorMethods['get' . Str::studly($key) . 'Attribute'] = $value;
                    $model->spreadableMutatorAttributes[$key] = $value;
                    $model->spreadableMutatorAttributes[Str::camel($key)] = $value;
                }
            }

        });

        self::retrieved(static function (Model $model) {
            // If there's a spread model, load its attributes
            if ($model->spreadable()->exists()) {
                $model->spreadableKeys = array_keys($model->spreadable?->content ?? []);
                $jsonData = $model->spreadable?->content ?? [];

                // Set spreadable attributes on model, excluding protected attributes
                // dd($jsonData, $model);
                foreach ($jsonData as $key => $value) {
                    if (! $model->isProtectedAttribute($key)) {
                        $model->append($key);
                        $model->spreadableMutatorMethods['get' . Str::studly($key) . 'Attribute'] = $value;
                        $model->spreadableMutatorAttributes[$key] = $value;
                        $model->spreadableMutatorAttributes[Str::camel($key)] = $value;
                    }
                }

                // Set _spread attribute
                // $model->setAttribute($model->getSpreadableSavingKey(), $jsonData);

            } else {
                // Initialize empty _spread if no spreadable exists
                // $model->setAttribute($model->getSpreadableSavingKey(), []);
            }
        });

        self::saved(static function (Model $model) {
            if ($model->spreadableIsUpdated && ! $model->wasChanged()) {
                $model->touch();
            }
        });

    }

    public function initializeHasSpreadable()
    {
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

    protected function getSpreadableClass(): \Illuminate\Database\Eloquent\Model
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

    // TODO: rename relation to spread as well
    public function spreadable(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        $spreadableClass = $this->getSpreadableClass();

        return $spreadableClass->morphOne(
            \Unusualify\Modularity\Entities\Spread::class,
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
        return $this->spreadableKeys;
    }

    final public static function getSpreadableSavingKey()
    {
        return static::$spreadableSavingKey ?? 'spread_payload';
    }
}
