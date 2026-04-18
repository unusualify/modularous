<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Unusualify\Modularity\Entities\Model;

trait SlugsTrait
{
    /**
     * When true, {@see RevisionsTrait::bypassAfterSaves} may set `passAfterSaveSlugsTrait` during pending-only
     * revision saves so {@see afterSaveSlugsTrait} is skipped.
     */
    protected bool $pendingBypassRevisionSlugsTrait = true;

    /**
     * Skip {@see HasSlug}'s automatic {@see setSlugs()} on {@see Model::saved} when the incoming
     * payload did not include any slug source attribute (or explicit `slugs`), so unrelated updates
     * do not rewrite the slug table or stringify object payloads.
     */
    public function beforeSaveSlugsTrait(Model $object, array $fields): void
    {
        if (! property_exists($this->model, 'slugAttributes')) {
            return;
        }

        $object->modularitySkipAutomaticSlugSync = $this->shouldSkipAutomaticSlugSyncOnSave($object, $fields);
    }

    /**
     * @param  array<string, mixed>  $fields  Raw request fields (before {@see prepareFieldsBeforeSave} transforms).
     */
    protected function shouldSkipAutomaticSlugSyncOnSave(Model $object, array $fields): bool
    {
        if (isset($fields['slugs']) && is_array($fields['slugs'])) {
            return false;
        }

        foreach ($object->getSlugAttributes() as $attr) {
            if (array_key_exists($attr, $fields)) {
                return false;
            }

            if (isset($fields['translations']) && is_array($fields['translations']) && array_key_exists($attr, $fields['translations'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Map per-locale slug attribute strings into {@see afterSaveSlugsTrait}'s `slugs` key.
     * Runs after {@see TranslationsTrait::prepareFieldsBeforeSaveTranslationsTrait} when repository uses both traits.
     *
     * @param Model $object
     * @param array $fields
     * @return array
     */
    public function prepareFieldsBeforeSaveSlugsTrait($object, $fields)
    {
        if (! property_exists($this->model, 'slugAttributes')) {
            return $fields;
        }

        $slugAttributes = $object->getSlugAttributes();

        if ($slugAttributes === []) {
            return $fields;
        }

        foreach (getLocales() as $locale) {
            foreach ($slugAttributes as $attr) {
                $slugPayload = $fields[$locale][$attr] ?? null;
                $hasSlugPayload = $slugPayload !== null && $slugPayload !== '';
                if (is_array($slugPayload)) {
                    $hasSlugPayload = isset($slugPayload['slug']) && $slugPayload['slug'] !== '';
                }
                if ($hasSlugPayload) {
                    $fields['slugs'][$locale] = $slugPayload;

                    break;
                }
            }
        }

        return $fields;
    }

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
                        ? true
                        : ($isArray && isset($slugValue['active']) ? (bool) $slugValue['active'] : true);
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

        if (! property_exists($this->model, 'slugAttributes')) {
            return $fields;
        }

        $slugAttributes = $object->getSlugAttributes();

        if ($slugAttributes === []) {
            return $fields;
        }

        $object->loadMissing('slugs');

        if ($object->slugs === null) {
            return $fields;
        }

        foreach ($slugAttributes as $attr) {
            foreach ($object->slugs as $slug) {
                if ($slug->active || $object->slugs->where('locale', $slug->locale)->where('active', true)->count() === 0) {
                    $fields['translations'][$attr][$slug->locale] = [
                        'slug' => $slug->slug,
                        'active' => (bool) $slug->active,
                    ];
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
