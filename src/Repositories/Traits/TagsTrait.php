<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Illuminate\Support\Arr;

trait TagsTrait
{
    public function setColumnsTagsTrait($columns, $inputs)
    {
        $traitName = get_class_short_name(__TRAIT__);

        $_columns = collect($inputs)->reduce(function ($acc, $curr) {
            if (preg_match('/tagger/', $curr['type'])) {
                $acc[] = $curr['name'];
            }

            return $acc;
        }, []);

        $columns[$traitName] = array_unique(array_merge($this->traitColumns[$traitName] ?? [], $_columns));

        return $columns;
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @return void
     */
    public function afterSaveTagsTrait($object, $fields)
    {
        $schema = $this->inputs() ?? [];

        if (! isset($fields['bulk_tags']) && ! isset($fields['previous_common_tags'])) {
            if (! $this->shouldIgnoreFieldBeforeSave('tags')) {
                $translated = false;
                if ($schema && isset($schema['tags']['translated']) && $schema['tags']['translated']) {
                    $translated = true;
                }

                $values = $fields['tags'] ?? [];

                if ($translated || Arr::isAssoc($values)) {
                    foreach ($values as $locale => $value) {
                        $object->setLocaleTags(tags: $value, locale: $locale);
                    }
                } else {
                    $object->setTags($fields['tags'] ?? []);
                }
            }

        } else {
            if (! $this->shouldIgnoreFieldBeforeSave('bulk_tags')) {
                $previousCommonTags = $fields['previous_common_tags']->pluck('name')->toArray();

                if (! empty($previousCommonTags)) {
                    if (! empty($difference = array_diff($previousCommonTags, $fields['bulk_tags'] ?? []))) {
                        $object->untag($difference);
                    }
                }

                $object->tag($fields['bulk_tags'] ?? []);
            }
        }
    }

    public function getFormFieldsTagsTrait($object, $fields, $schema = null)
    {
        if ($object->has('tags')) {
            foreach ($this->getColumns(__TRAIT__) as $column) {
                $translated = false;

                $tagInput = $schema[$column] ?? null;

                if ($tagInput && isset($tagInput['translated']) && $tagInput['translated']) {
                    $translated = true;
                }

                if ($translated) {
                    $locales = getLocales();
                    $fields[$column] = $object->tags->groupBy('locale')->map(function ($group) {
                        return $group->map(fn ($tag) => $tag->name);
                    });

                    foreach ($locales as $locale) {
                        $fields[$column][$locale] = $fields[$column][$locale] ?? ($tagInput && $tagInput['default'] ? $tagInput['default'] : ($tagInput && ($tagInput['multiple'] ?? true) ? collect([]) : null));
                    }
                } else {
                    $fields[$column] = $object->tags->map(fn ($tag) => $tag->name);
                }
            }
        }

        return $fields;
    }

    protected function filterTagsTrait(&$query, &$scopes)
    {
        $this->addRelationFilterScope($query, $scopes, 'tag_id', 'tags');
    }

    protected function getTagsQuery()
    {
        return $this->model->allTags()->orderBy('count', 'desc');
    }

    /**
     * @param string $query
     * @param array $ids
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTags($query = '', $ids = [], $translated = false, ?callable $map = null)
    {
        $tagQuery = $this->getTagsQuery();

        if (! empty($query)) {
            $tagQuery->where('slug', 'like', '%' . $query . '%');
        }

        // dd($tagQuery->toRawSql());
        if (! empty($ids)) {
            foreach ($ids as $id) {
                $tagQuery->whereHas(modularityConfig('tables.tagged', 'tagged'), function ($query) use ($id) {
                    $query->where('taggable_id', $id);
                });
            }
        }

        $result = $tagQuery->get();

        if ($translated) {
            $result = $result->groupBy('locale');

            $locales = getLocales();

            foreach ($locales as $locale) {
                $result[$locale] = $result[$locale] ?? collect([]);
            }

            if ($map) {
                $result = $result->map(function ($group) use ($map) {
                    return $group->map($map);
                });
            }
        } elseif ($map) {
            $result = $result->map($map);
        }

        return $result;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getTagsList()
    {
        return $this->getTagsQuery()->where('count', '>', 0)->select('name', 'id')->get()->map(function ($tag) {
            return [
                'label' => $tag->name,
                'value' => $tag->id,
            ];
        });
    }
}
