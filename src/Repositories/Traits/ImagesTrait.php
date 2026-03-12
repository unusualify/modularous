<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Unusualify\Modularity\Entities\Media;

trait ImagesTrait
{
    public function setColumnsImagesTrait($columns, $inputs)
    {

        $traitName = get_class_short_name(__TRAIT__);

        $columns[$traitName] = collect($inputs)->reduce(function ($acc, $curr) {
            if (preg_match('/image/', $curr['type'])) {
                $acc[] = $curr['name'];
            }

            return $acc;
        }, []);

        return $columns;
    }

    /**
     * @param \Unusualify\Modularity\Entities\Model $object
     * @param array $fields
     * @return \Unusualify\Modularity\Entities\Model
     */
    public function hydrateImagesTrait($object, $fields)
    {
        // dd('hydrateImagesTrait', $object, $fields, $this->getMedias($fields));
        if ($this->shouldIgnoreFieldBeforeSave('medias')) {
            return $object;
        }

        $mediasCollection = Collection::make();

        $mediasFromFields = $this->getMedias($object, $fields);

        $mediasFromFields->each(function ($media) use ($object, $mediasCollection) {
            $newMedia = Media::withTrashed()->find(is_array($media['media_id']) ? Arr::first($media['media_id']) : $media['media_id']);
            $pivot = $newMedia->newPivot($object, Arr::except($media, ['id']), modularityConfig('tables.mediables', 'umod_mediables'), true);
            $newMedia->setRelation('pivot', $pivot);
            $mediasCollection->push($newMedia);
        });

        $object->setRelation('medias', $mediasCollection);

        return $object;
    }

    /**
     * @param \Unusualify\Modularity\Entities\Model $object
     * @param array $fields
     * @return void
     */
    public function afterSaveImagesTrait($object, $fields)
    {
        if ($this->shouldIgnoreFieldBeforeSave('medias')) {
            return;
        }

        $this->getMedias($object, $fields)->each(function ($media) use ($object) {
            if (isset($media['id']) && $media['id']) {
                $result = $object->medias()->updateExistingPivot($media['id'], Arr::except($media, ['id', 'media_id']));
                if ($result) {
                    $this->mustTouchEloquentModel();
                }
            } else {
                $object->medias()->attach($media['media_id'], Arr::except($media, ['media_id']));
                $this->mustTouchEloquentModel();
            }
        });
    }

    /**
     * @param \Unusualify\Modularity\Entities\Model $object
     * @param array $fields
     * @return array
     */
    public function getFormFieldsImagesTrait($object, $fields, $schema)
    {
        // $t = [];
        if ($object->has('medias')) {
            $schema = $schema ?? $this->inputs();
            $mediasByRole = $object->medias->groupBy('pivot.role');
            $default_locale = config('app.locale');
            $fallback_locale = config('app.fallback_locale');

            foreach ($this->getColumns(__TRAIT__) as $role) {
                if (isset($mediasByRole[$role])) {
                    $input = $schema[$role];
                    if ($input['translated'] ?? false) {
                        foreach ($mediasByRole[$role]->groupBy('pivot.locale') as $locale => $mediasByLocale) {
                            $fields[$role][$locale] = $mediasByLocale->map(function ($media) {
                                return $media->mediableFormat();
                            });
                        }
                    } else {
                        $medias = $mediasByRole[$role]->groupBy('pivot.locale')[$default_locale] ?? $mediasByRole[$role]->groupBy('pivot.locale')[$fallback_locale] ?? collect([]);
                        $fields[$role] = $medias->map(function ($media) {
                            return $media->mediableFormat();
                        });
                    }
                } else {
                    $input = $schema[$role] ?? null;

                    if ($input) {
                        $fields += [
                            $input['name'] => ($input['translated'] ?? false) ? Collection::make(Arr::mapWithKeys(getLocales(), function ($locale) {
                                return [$locale => []];
                            })) : Collection::make([]),
                        ];
                    }
                }
            }
        }

        return $fields;
    }

    /**
     * @param array $fields
     * @return \Illuminate\Support\Collection
     */
    private function getMedias($object, $fields)
    {
        $images = Collection::make();

        $systemLocales = getLocales();

        $imageRoles = $this->getColumns(__TRAIT__);

        foreach ($imageRoles as $role) {
            if (isset($fields[$role])) {
                foreach ($systemLocales as $locale) {
                    if (isset($fields[$role][$locale])) {
                        $images = $this->pushImage($object, $images, $fields[$role][$locale], $role, $locale);
                    } else {
                        $images = $this->pushImage($object, $images, $fields[$role], $role, $locale);

                    }
                }
            }
        }

        return $images;
    }

    public function pushImage($object, $images, $imagesData, $role, $locale, $index = null)
    {
        $mediablesTable = modularityConfig('tables.mediables', 'um_mediables');
        Collection::make($imagesData)->each(function ($image) use ($object, $mediablesTable, &$images, $role, $locale, $index) {
            $replacePattern = '/([A-Za-z-_]+)(\.)(\*)(\.)([A-Za-z-_\.]+)/';
            $role = preg_replace($replacePattern, '${1}${2}' . $index . '${4}${5}', $role);
            $mediableId = $object->medias()
                ->select($mediablesTable . '.id as pivot_id')
                ->where('media_id', $image['id'])
                ->where('role', $role)
                ->where('locale', $locale)->value('pivot_id') ?? null;

            $images->push([
                ...($mediableId ? ['id' => $mediableId] : []),
                'media_id' => $image['id'],
                'role' => $role,
                'metadatas' => json_encode($image['metadatas']),
                'crop' => 'default',
                'locale' => $locale,
            ]);
        });

        return $images;
    }

    /**
     * @param string $role
     * @return array
     */
    public function getCrops($role)
    {
        return $this->model->mediasParams[$role];
    }
}
