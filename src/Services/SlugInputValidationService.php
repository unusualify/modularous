<?php

namespace Unusualify\Modularity\Services;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Unusualify\Modularity\Entities\Traits\HasSlug;
use Unusualify\Modularity\Facades\Modularity;

/**
 * Validates slug strings for admin form inputs (uniqueness against slug tables, optional locale scope).
 */
class SlugInputValidationService
{
    /**
     * Resolve module route to model and validate slug value.
     *
     * @return array{valid: bool, message: ?string, normalized: string}
     */
    public function validate(
        string $moduleName,
        string $routeName,
        string $value,
        ?string $locale = null,
        bool $localeScoped = true,
        ?int $excludeId = null,
    ): array {
        $modelClass = $this->resolveModelClass($moduleName, $routeName);

        return $this->validateModelSlug($modelClass, $value, $locale, $localeScoped, $excludeId);
    }

    /**
     * Validate slug for a concrete model class (used by HTTP layer and tests).
     *
     * @param class-string<\Illuminate\Database\Eloquent\Model> $modelClass
     * @return array{valid: bool, message: ?string, normalized: string}
     */
    public function validateModelSlug(
        string $modelClass,
        string $value,
        ?string $locale = null,
        bool $localeScoped = true,
        ?int $excludeId = null,
    ): array {
        if (! in_array(HasSlug::class, class_uses_recursive($modelClass), true)) {
            return [
                'valid' => false,
                'message' => __('This entity does not support slug validation.'),
                'normalized' => '',
            ];
        }

        $model = new $modelClass;

        $locale = $locale ?? app()->getLocale();

        $normalized = $this->normalizeSlugForModel($model, $value, $locale);

        if ($normalized === '') {
            return [
                'valid' => false,
                'message' => __('The slug field is required.'),
                'normalized' => '',
            ];
        }

        $slugModelClass = $model->getSlugModelClass();
        $foreignKey = $model->getForeignKey();

        $query = $slugModelClass::query()
            ->where('slug', $normalized);

        if ($localeScoped) {
            $query->where('locale', $locale);
        }

        if ($excludeId !== null) {
            $query->where($foreignKey, '!=', $excludeId);
        }

        if ($query->exists()) {
            return [
                'valid' => false,
                'message' => __('This slug is already taken.'),
                'normalized' => $normalized,
            ];
        }

        return [
            'valid' => true,
            'message' => null,
            'normalized' => $normalized,
        ];
    }

    /**
     * @throws InvalidArgumentException
     */
    public function resolveModelClass(string $moduleName, string $routeName): string
    {
        $module = Modularity::find($moduleName);

        if ($module === null) {
            throw new InvalidArgumentException(__('The specified module was not found.'));
        }

        return $module->getRouteClass($routeName, 'model');
    }

    /**
     * Match {@see HasSlug} slug generation for the given locale.
     */
    protected function normalizeSlugForModel($model, string $raw, string $locale): string
    {
        $trimmed = trim($raw);

        if ($trimmed === '') {
            return '';
        }

        if (in_array($locale, modularityConfig('slug_utf8_languages', []), true)) {
            return $model->getUtf8Slug($trimmed);
        }

        return Str::slug($trimmed);
    }
}
