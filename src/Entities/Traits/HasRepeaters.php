<?php

namespace Unusualify\Modularous\Entities\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Unusualify\Modularous\Entities\Repeater;

/**
 * @author Hazarcan Doğa
 *
 * @version ${1:1.0.0}
 *
 * @since 08 Jan 2024
 *
 * @lastModifiedBy Hazarcan Doğa
 */
trait HasRepeaters
{
    use HasFiles, HasImages, HasPriceable, HasFileponds;

    private ?array $repeaterRoles = null;

    private ?array $repeaterLocaleRoles = null;

    private function resolveRepeaterRoles(): void
    {
        if ($this->repeaterRoles !== null) {
            return;
        }

        $repeaterFields = $this->exists
            ? $this->repeaters()->select('role', 'locale')->get()
            : collect();

        $this->repeaterLocaleRoles = $repeaterFields->groupBy('locale')->map(function ($group) {
            return $group->pluck('role')->unique()->values()->toArray();
        })->toArray();
        $this->repeaterRoles = $repeaterFields->pluck('role')->unique()->values()->toArray();
    }

    /**
     * Defines the one-to-many relationship between the module and Repeater.
     * Get all repeaters belonging to a module.
     *
     * @uses  \Unusualify\Modularous\Entities\Repeater::class
     */
    public function repeaters($role = null, $locale = null): MorphMany
    {
        $query = $this->morphMany(
            Repeater::class,
            'repeatable',
        );

        if ($role) {
            $query->where('role', $role);
        }

        if ($locale) {
            $query->where('locale', $locale);
        }

        return $query;
    }

    private function parseRepeaterField($field): array
    {
        $parts = explode('.', $field);
        $role = array_shift($parts);
        $contentArrowNotation = implode('->', $parts);
        $contentDotNotation = implode('.', $parts);
        array_unshift($parts, 'content');
        $fullArrowNotation = implode('->', $parts);
        $fullDotNotation = implode('.', $parts);

        return [$role, $contentArrowNotation, $contentDotNotation, $fullArrowNotation, $fullDotNotation];
    }

    public function getRepeaterField($field, $locale = null, $default = null): mixed
    {
        $locale = $locale ?? app()->getLocale();

        [$role, $contentArrowNotation, $contentDotNotation, $fullArrowNotation, $fullDotNotation] = $this->parseRepeaterField($field);

        $repeater = $this->repeaters($role, $locale)->first();

        if (! $repeater) {
            return $default;
        }

        return data_get_with_dot_keys($repeater->content, $contentDotNotation, $default);
    }

    public function getRepeaterRoles(): array
    {
        $this->resolveRepeaterRoles();

        return $this->repeaterRoles;
    }

    public function getRepeaterLocaleRoles(): array
    {
        $this->resolveRepeaterRoles();

        return $this->repeaterLocaleRoles;
    }

    public function hasRepeaterRole(string $role): bool
    {
        return in_array($role, $this->getRepeaterRoles());
    }

    public function hasRepeaterLocaleRole(string $role, ?string $locale = null): bool
    {
        $locale = $locale ?? app()->getLocale();

        return in_array($role, $this->getRepeaterLocaleRoles()[$locale] ?? []);
    }

    // public function emptyRepeaterLocaleRole(string $role, ?string $locale = null): bool
    // {
    //     $locale = $locale ?? app()->getLocale();

    //     return !$this->repeaters($role, $locale)->exists() && $this->repeaters($role, $locale)->whereJsonLength('content', 0)->exists();
    //     return !$this->repeaters($role, $locale)->exists() && $this->repeaters($role, $locale)->where('content', DB::raw("json_array()"))->exists();
    // }

    // public function notEmptyRepeaterLocaleRole(string $role, ?string $locale = null): bool
    // {
    //     $locale = $locale ?? app()->getLocale();

    //     return !$this->emptyRepeaterLocaleRole($role, $locale);
    // }

    public function isRepeaterValueEqual(string $key, string $value, ?string $locale = null): bool
    {
        $locale = $locale ?? app()->getLocale();
        $repeaterLocaleRoles = $this->getRepeaterLocaleRoles();
        if (! isset($repeaterLocaleRoles[$locale])) {
            return false;
        }

        [$role, $contentArrowNotation, $contentDotNotation, $fullArrowNotation, $fullDotNotation] = $this->parseRepeaterField($key);

        return $this->relationLoaded('repeaters') ? $this->repeaters->contains(function ($repeater) use ($role, $contentDotNotation, $value) {
            return $repeater->role === $role && (($v = data_get_with_dot_keys($repeater->content, $contentDotNotation)) ? $v === $value : false);
        }) : $this->repeaters()->where('role', $role)->whereJsonContains($fullArrowNotation, $value)->exists();
    }
}
