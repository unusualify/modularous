<?php

namespace Modules\Cms\Repositories;

use Modules\Cms\Entities\SiteSetting;
use Unusualify\Modularous\Repositories\Repository;

class SiteSettingRepository extends Repository
{
    public function __construct(SiteSetting $model)
    {
        $this->model = $model;
    }

    /**
     * Single site-setting row for the given composite key (includes soft-deleted for restore semantics).
     */
    public function findScoped(string $groupKey, string $key, string $locale): ?SiteSetting
    {
        return $this->model->newQuery()
            ->withTrashed()
            ->where('group_key', $groupKey)
            ->where('key', $key)
            ->where('locale', $locale)
            ->first();
    }

    /**
     * Persist a value or remove the row when {@code $value} is null or whitespace-only (revert to env/config fallback).
     */
    public function putScoped(string $groupKey, string $key, string $locale, ?string $value): void
    {
        if ($value === null || trim($value) === '') {
            $existing = $this->findScoped($groupKey, $key, $locale);
            if ($existing !== null) {
                $existing->forceDelete();
            }

            return;
        }

        $model = $this->findScoped($groupKey, $key, $locale) ?? $this->model->newInstance([
            'group_key' => $groupKey,
            'key' => $key,
            'locale' => $locale,
        ]);

        if ($model->trashed()) {
            $model->restore();
        }

        $model->fill([
            'value' => $value,
            'is_active' => true,
        ]);
        $model->save();
    }
}
