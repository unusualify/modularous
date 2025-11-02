<?php

namespace Unusualify\Modularity\Entities\Traits\Core;

use Cartalyst\Tags\TaggableTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Unusualify\Modularity\Entities\Casts\LocaleTagsCast;

trait LocaleTags
{
    public static $localeTagsField = 'locale_tags_payload';

    private $localeTagsUpdatingPayload = null;

    public static function bootLocaleTags()
    {
        static::retrieved(function ($model) {
            // Register a custom cast for the dynamic locale tags field
            $fieldName = $model::$localeTagsField;

            if(isset(self::$loadLocalizedTags) && self::$loadLocalizedTags === true) {
                $model->mergeCasts([
                    $fieldName => LocaleTagsCast::class,
                ]);
            }
        });

        static::saving(function ($model) {
            $fieldName = $model::$localeTagsField;

            // Check if the virtual attribute was set (in attributes array)
            if($model->offsetExists($fieldName)) {
                $model->localeTagsUpdatingPayload = $model->getAttributes()[$fieldName];
            }
            // Remove from attributes so it doesn't try to save to DB
            $model->offsetUnset($fieldName);
        });

        static::saved(function ($model) {
            if($model->localeTagsUpdatingPayload) {
                foreach($model->localeTagsUpdatingPayload as $locale => $tags) {
                    $model->setLocaleTags($tags, locale: $locale);
                }
            }
        });
    }

    public function initializeLocaleTags()
    {
        if(isset($this->allowLocaleTagsFillable) && $this->allowLocaleTagsFillable) {
            $this->mergeFillable([
                self::$localeTagsField
            ]);
        }
    }

    public static function allLocaleTags(?string $locale = null): \Illuminate\Database\Eloquent\Builder
    {
        $instance = new static();
        $tagsModel = $instance->createTagsModel();
        $query = $tagsModel->whereNamespace($instance->getEntityClassName());
        if ($locale !== null) { $query->where($tagsModel->getTable() . '.locale', $locale); }
        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public static function scopeWhereLocaleTag(Builder $query, $tags, string $type = 'slug', $locale = null): Builder
    {
        $locale = $locale ?: app()->getLocale();
        $tags = (new static())->prepareTags($tags);

        foreach ($tags as $tag) {
            $query->whereHas('tags', function ($query) use ($type, $tag, $locale) {
                $query->where($type, $tag)->where('locale', $locale);
            });
        }

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public static function scopeWithLocaleTag(Builder $query, $tags, string $type = 'slug', $locale = null): Builder
    {
        $locale = $locale ?: app()->getLocale();
        $tags = (new static())->prepareTags($tags);

        return $query->whereHas('tags', function ($query) use ($type, $tags, $locale) {
            $query->whereIn($type, $tags)->where('locale', $locale);
        });
    }

    /**
     * {@inheritdoc}
     */
    public static function scopeWithoutLocaleTag(Builder $query, $tags, string $type = 'slug', $locale = null): Builder
    {
        $locale = $locale ?: app()->getLocale();
        $tags = (new static())->prepareTags($tags);

        return $query->whereDoesntHave('tags', function ($query) use ($type, $tags, $locale) {
            $query->whereIn($type, $tags)->where('locale', $locale);
        });
    }

    public function addLocaleTag(string $name, ?string $locale): void
    {
        $locale = $locale ?: app()->getLocale();

        $tag = $this->createTagsModel()->newQuery()->firstOrNew([
            'slug' => $this->generateLocaleTagsSlug($name, $locale),
            'namespace' => $this->getEntityClassName(),
            'locale' => $locale,
        ]);

        if (! $tag->exists) {
            $tag->name = $name;
            $tag->save();
        }

        $tagsTable = $this->createTagsModel()->getTable();

        $alreadyAttached = $this->tags()
            ->where($tagsTable . '.id', $tag->id)
            ->where($tagsTable . '.locale', $locale)
            ->exists();

        if (! $alreadyAttached) {
            $tag->update(['count' => $tag->count + 1]);
            $this->tags()->attach($tag->id);
        }

        $this->load('tags');
        $this->load('localeTags');
    }

    public function untagLocale($tags = null, $locale = null): bool
    {
        $locale = $locale ?: app()->getLocale();
        $tags = $tags ?: $this->tags->pluck('name')->all();

        foreach ($this->prepareTags($tags) as $name) {
            $this->removeLocaleTag($name, $locale);
        }
        return true;
    }

    /**
     * Tag current model with given tags, scoped by locale.
     * @param array|string $tags
     * @param string|null $locale
     * @return bool
     */
    public function tagLocale($tags, $locale = null): bool
    {
        $locale = $locale ?: app()->getLocale();
        foreach ($this->prepareTags($tags) as $tagName) {
            $this->addLocaleTag($tagName, $locale);
        }
        return true;
    }

    public function removeLocaleTag(string $name, ?string $locale): void
    {
        $locale = $locale ?: app()->getLocale();
        $slug = $this->generateLocaleTagsSlug($name, $locale);
        $namespace = $this->getEntityClassName();

        $tag = $this->createTagsModel()->newQuery()
            ->whereNamespace($namespace)
            ->where('slug', $slug)
            ->where('locale', $locale)
            ->first();

        $tagsTable = $this->createTagsModel()->getTable();

        if ($tag) {
            $pivot = $this->tags()
                ->where($tagsTable . '.id', $tag->id)
                ->where($tagsTable . '.locale', $locale);

            if ($pivot->exists()) {
                $tag->update(['count' => max(0, $tag->count - 1)]);
                $pivot->detach($tag);
            }
        }

        $this->load('tags');
        $this->load('localeTags');
    }

    public function setLocaleTags($tags, string $type = 'name', $locale = null): bool
    {
        $locale = $locale ?: app()->getLocale();

        $tags = is_string($tags) ? [$tags] : $tags;

        $tags = $this->prepareTags($tags);

        $entityTags = $this->tags()->where('locale', $locale)->pluck($type)->all();

        $tagsToAdd = array_diff($tags, $entityTags);
        $tagsToDel = array_diff($entityTags, $tags);

        if (! empty($tagsToDel)) {
            $this->untagLocale($tagsToDel, $locale);
        }

        if (! empty($tagsToAdd)) {
            $this->tagLocale($tagsToAdd, $locale);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function localeTags($locale = null): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        $locale = $locale ?: app()->getLocale();
        $tagsModel = $this->createTagsModel();

        return $this->morphToMany(
            static::$tagsModel,
            'taggable',
            modularityConfig('tables.tagged', 'tagged'),
            'taggable_id',
            'tag_id'
        // )->withPivot(['locale']);
        )->where($tagsModel->getTable() . '.locale', $locale);
    }

    /**
     * Get the list of tags for all locales
     * @return Collection
     */
    public function localeTagsList(): Collection
    {
        $locales = getLocales();
        $tags = Collection::make([]);

        foreach ($locales as $locale) {
            $tags->put($locale, $this->createTagsModel()->newQuery()->whereNamespace($this->getEntityClassName())->where('locale', $locale)->get());
        }

        return $tags;
    }

    /**
     * Get the dictionary of tags for the model
     * @return array
     */
    public function getLocaleTagsDictionary(): array
    {
        return [];
    }

    public function generateLocaleTagsSlug(string $name, ?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        $dictionary = $this->getLocaleTagsDictionary() ?? [];

        return call_user_func(static::$localeTagsSlugGenerator ?? 'Illuminate\Support\Str::modularitySlug', ...[
            $name,
            'language' => $locale,
            'dictionary' => $dictionary,
        ]);
    }
}
