<?php

namespace Unusualify\Modularous\Entities;

use Carbon\Carbon;
use Cartalyst\Tags\TaggableInterface;
use Cartalyst\Tags\TaggableTrait;
use Illuminate\Database\Eloquent\Model as LaravelModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;
// use Modules\Notification\Events\ModelCreated;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Unusualify\Modularous\Contracts\Cache\CacheableInterface;
use Unusualify\Modularous\Contracts\ModuleableInterface;
use Unusualify\Modularous\Entities\Traits\Core\HasCaching;
use Unusualify\Modularous\Entities\Traits\Core\LocaleTags;
use Unusualify\Modularous\Entities\Traits\Core\ModelHelpers;
use Unusualify\Modularous\Entities\Traits\IsTranslatable;
use Unusualify\Modularous\Traits\Traitify;

abstract class Model extends LaravelModel implements CacheableInterface, ModuleableInterface, TaggableInterface
{
    use IsTranslatable,
        ModelHelpers,
        SoftDeletes,
        TaggableTrait,
        LocaleTags,
        Notifiable,
        HasCaching,
        Traitify;

    public $timestamps = true;

    protected static string $builder = Builder::class;

    private static bool $deferRetrievedEvents = false;

    // protected $dispatchesEvents = [
    //     'created' => ModelCreated::class,
    // ];

    protected function isTranslationModel(): bool
    {
        return Str::endsWith(get_class($this), 'Translation') || property_exists($this, 'baseModuleModel');
    }

    // public function setPublishStartDateAttribute($value): void
    // {
    //     $this->attributes['publish_start_date'] = $value ?? Carbon::now();
    // }

    public function getFillable(): array
    {
        // If the fillable attribute is filled, just use it
        $fillable = $this->fillable;

        // If fillable is empty
        // and it's a translation model
        // and the baseModel was defined
        // Use the list of translatable attributes on our base model
        static $translationFillables = [];
        static $usesAuthorizable = [];

        if (
            blank($fillable) &&
            $this->isTranslationModel() &&
            property_exists($this, 'baseModuleModel')
        ) {
            $fillable = $translationFillables[static::class] ??= (function () {
                $fillable = (new $this->baseModuleModel)->getTranslatedAttributes();

                if (! in_array('locale', $fillable, true)) {
                    $fillable[] = 'locale';
                }

                if (! in_array('active', $fillable, true)) {
                    $fillable[] = 'active';
                }

                return $fillable;
            })();
        }

        if ($usesAuthorizable[static::class] ??= in_array('Unusualify\Modularous\Entities\Traits\HasAuthorizable', class_uses_recursive(static::class))) {
            $fillable = array_merge($fillable, static::$hasAuthorizableFillable ?? []);
        }

        // if (in_array('Unusualify\Modularous\Entities\Traits\HasStateable', class_uses_recursive(static::class))) {
        //     $fillable = array_merge($fillable, static::$hasStateableFillable ?? []);
        // }

        return $fillable;
    }

    // public function getTranslatedAttributes()
    // {
    //     return $this->translatedAttributes ?? [];
    // }

    protected static function bootTaggableTrait()
    {
        static::$tagsModel = Tag::class;
    }

    /**
     * {@inheritdoc}
     */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(
            static::$tagsModel,
            'taggable',
            modularousConfig('tables.tagged', 'tagged'),
            'taggable_id',
            'tag_id'
        );
    }

    /**
     * Collect relationship names and types from method return types only.
     *
     * The ManageEloquent boot invoked every relationship method to read the related
     * model, which booted each related model class (recursively) on every request.
     * Those details are only needed by getEloquentRelationships(), so they are
     * resolved lazily there.
     */
    public static function bootManageEloquent()
    {
        $relationClassesPattern = '|' . preg_quote(config('manage-eloquent.relations_namespace'), '|') . '|';
        $relationships = [];

        foreach ((new \ReflectionClass(static::class))->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if (! $method->hasReturnType() || ! preg_match($relationClassesPattern, $returnType = (string) $method->getReturnType())) {
                continue;
            }

            try {
                $relationClass = new \ReflectionClass($returnType);
            } catch (\ReflectionException $e) {
                continue;
            }

            $relationships[$method->name] = [
                'relationship_class' => $relationClass->getName(),
                'short_relationship_class' => $relationClass->getShortName(),
            ];
        }

        static::$manageEloquentDefinedRelationships[static::class] = $relationships;
    }

    public function getEloquentRelationships($relations = null): array
    {
        $class = static::class;
        $definedRelationships = static::$manageEloquentDefinedRelationships[$class] ?? [];
        $blank = null;

        foreach ($definedRelationships as $name => $definition) {
            if (array_key_exists('relationship_model', $definition)) {
                continue;
            }

            $details = static::resolveEloquentRelationshipDetails($blank ??= new static, $name);

            if ($details === null) {
                unset($definedRelationships[$name]);
            } else {
                $definedRelationships[$name] = $definition + $details;
            }
        }

        static::$manageEloquentDefinedRelationships[$class] = $definedRelationships;

        if (is_array($relations)) {
            return array_keys(Arr::where($definedRelationships, fn ($val) => in_array($val, $relations)));
        }

        if (is_string($relations) && $relations !== '') {
            return array_keys(Arr::where($definedRelationships, fn ($val) => $val == Str::studly($relations)));
        }

        return $definedRelationships;
    }

    protected static function resolveEloquentRelationshipDetails(LaravelModel $model, string $name): ?array
    {
        try {
            $relationship = $model->{$name}();
            $related = $relationship->getRelated();
        } catch (\Exception $e) {
            return null;
        }

        $isManyToMany = false;
        $hasMiddlemanModel = false;
        $middlemanModel = null;
        $middlemanTable = null;

        if ($relationship instanceof MorphToMany) {
            $isManyToMany = true;
            $middlemanTable = $relationship->getTable();
        } elseif ($relationship instanceof BelongsToMany) {
            $isManyToMany = true;
            $pivotClass = $relationship->getPivotClass();

            if (! ($pivotClass instanceof Pivot)) {
                $hasMiddlemanModel = true;
                $middlemanModel = $pivotClass;
            }

            $middlemanTable = $relationship->getTable();
        } elseif ($relationship instanceof HasManyThrough) {
            $isManyToMany = true;
            $hasMiddlemanModel = true;
            $middlemanModel = get_class($relationship->getParent());
            $middlemanTable = $relationship->getParent()->getTable();
        }

        return [
            'relationship_model' => get_class($related),
            'relationship_table' => $related->getTable(),
            'is_many_to_many' => $isManyToMany,
            'has_middleman' => $hasMiddlemanModel,
            'middleman_model' => $middlemanModel,
            'middleman_table' => $middlemanTable,
        ];
    }

    /**
     * Override newInstance to propagate dependentWarmingEnabled to new instances.
     *
     * Laravel's Builder::create() calls $this->model->newInstance($attributes)
     * to build a fresh model before saving. By overriding this, the flag set via
     * preventDependentWarming() on the parent naturally flows to the child,
     * ensuring the observer sees the correct value on the actual persisted instance.
     *
     * @param array $attributes
     * @param bool $exists
     * @return static
     */
    public function newInstance($attributes = [], $exists = false)
    {
        static $methods = [];

        $instance = parent::newInstance($attributes, $exists);

        foreach ($methods[static::class] ??= $this->traitsMethods(__FUNCTION__) as $method) {
            $instance = $this->$method($instance, $attributes, $exists);
        }

        return $instance;
    }

    /**
     * @param array $attributes
     * @param string|null $connection
     * @return static
     */
    public function newFromBuilder($attributes = [], $connection = null)
    {
        if (! self::$deferRetrievedEvents) {
            return parent::newFromBuilder($attributes, $connection);
        }

        $model = $this->newInstance([], true);

        $model->setRawAttributes((array) $attributes, true);

        $model->setConnection($connection ?? $this->getConnectionName());

        return $model;
    }

    /**
     * Run the callback while newFromBuilder() skips the `retrieved` event; the caller
     * becomes responsible for firing it via fireRetrievedEvent().
     *
     * @template T
     *
     * @param callable(): T $callback
     * @return T
     */
    public static function withDeferredRetrievedEvents(callable $callback): mixed
    {
        $previous = self::$deferRetrievedEvents;
        self::$deferRetrievedEvents = true;

        try {
            return $callback();
        } finally {
            self::$deferRetrievedEvents = $previous;
        }
    }

    public function fireRetrievedEvent(): void
    {
        $this->fireModelEvent('retrieved', false);
    }

    /**
     * `retrievedBatch{Trait}(Collection $models)` hooks, run before `retrieved` fires for a hydrated result set.
     *
     * @return string[]
     */
    public static function retrievedBatchMethods(): array
    {
        static $methods = [];

        return $methods[static::class] ??= array_values(static::staticTraitsMethods('retrievedBatch'));
    }
}
