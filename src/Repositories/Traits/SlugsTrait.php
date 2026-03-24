<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Unusualify\Modularity\Entities\Model;

trait SlugsTrait
{
    /**
     * @param Model $object
     * @param array $fields
     * @return void
     */
    public function afterSaveSlugsTrait($object, $fields)
    {
        if (property_exists($this->model, 'slugAttributes')) {
            foreach (getLocales() as $locale) {
                if (isset($fields['slugs']) && isset($fields['slugs'][$locale]) && ! empty($fields['slugs'][$locale])) {
                    $slugValue = $fields['slugs'][$locale];
                    $isArray = is_array($slugValue);
                    $object->disableLocaleSlugs($locale);
                    $currentSlug = [];
                    $currentSlug['slug'] = $isArray ? $slugValue['slug'] : $slugValue;
                    $currentSlug['locale'] = $locale;
                    $currentSlug['active'] = ($this->model->isTranslatable() && isset($object->translations) && count($object->translations) > 0 && ! ($isArray && isset($slugValue['active'])))
                        ? $object->translate($locale)->active
                        : ($isArray && isset($slugValue['active']) ? (bool) $slugValue['active'] : 1);
                    $currentSlug = $this->getSlugParameters($object, $fields, $currentSlug);

                    $object->updateOrNewSlug($currentSlug);
                }
            }
        }
    }

    /**
     * @param Model $object
     * @return void
     */
    public function afterDeleteSlugsTrait($object)
    {
        $object->slugs()->delete();
    }

    /**
     * @param Model $object
     * @return void
     */
    public function afterRestoreSlugsTrait($object)
    {
        $object->slugs()->restore();
    }

    /**
     * @param Model $object
     * @param array $fields
     * @return array
     */
    public function getFormFieldsSlugsTrait($object, $fields)
    {
        unset($fields['slugs']);

        if ($object->slugs != null) {
            foreach ($object->slugs as $slug) {
                if ($slug->active || $object->slugs->where('locale', $slug->locale)->where('active', true)->count() === 0) {
                    $fields['translations']['slug'][$slug->locale] = $slug->slug;
                }
            }
        }

        return $fields;
    }

    /**
     * @param Model $object
     * @param array $fields
     * @param array $slug
     * @return array
     */
    public function getSlugParameters($object, $fields, $slug)
    {
        $slugParams = $this->getSlugParams($object, $slug['locale']);

        foreach ($object->getSlugAttributes() as $param) {
            if (isset($slugParams[$param]) && isset($fields[$param])) {
                $slug[$param] = $fields[$param];
            } elseif (isset($slugParams[$param])) {
                $slug[$param] = $slugParams[$param];
            }
        }

        return $slug;
    }

    /**
     * @param string $slug
     * @param array $with
     * @param array $withCount
     * @param array $scopes
     * @return Model|null
     */
    public function existsSlug($slug, $with = [], $withCount = [], $scopes = [])
    {
        $query = $this->model->where($scopes)->scopes(['published', 'visible']);

        foreach (class_uses_recursive(get_called_class()) as $trait) {
            if (method_exists(get_called_class(), $method = 'getPublishedScopes' . class_basename($trait))) {
                $query->scopes($this->$method());
            }
        }

        $item = (clone $query)->existsSlug($slug)->with($with)->withCount($withCount)->first();

        if (! $item && $item = (clone $query)->orWhere(function ($query) use ($slug) {
            return $query->existsInactiveSlug($slug);
        })->first()) {
            $item->redirect = true;
        }

        if (! $item && config('translatable.use_property_fallback', false)
        && config('translatable.fallback_locale') != config('app.locale')) {
            $item = (clone $query)->orWhere(function ($query) {
                return $query->withActiveTranslations(config('translatable.fallback_locale'));
            })->existsFallbackLocaleSlug($slug)->first();

            if ($item) {
                $item->redirect = true;
            }
        }

        return $item;
    }

    /**
     * @param string $slug
     * @param array $with
     * @param array $withCount
     * @return Model
     */
    public function existsSlugPreview($slug, $with = [], $withCount = [])
    {
        return $this->model->existsInactiveSlug($slug)->with($with)->withCount($withCount)->first();
    }

    protected function getSlugParams($object, $locale)
    {
        return $object->getSlugParams($locale);
    }
}
