<?php

declare(strict_types=1);

namespace Unusualify\Modularity\Entities\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

final class LocaleTagsCast implements CastsAttributes
{
    /**
     * Cast the given value (acts as an accessor).
     *
     * @param array<string, mixed> $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        $locales = getLocales();
        $singularLocaleTags = $model->singularLocaleTags ?? false;

        $tags = collect([]);
        foreach ($locales as $locale) {
            $tags->put($locale, $model->localeTags($locale)->get());
        }

        return $tags
            ->map(function ($tags, $locale) use ($singularLocaleTags) {
                if ($singularLocaleTags) {
                    return $tags->first()?->name;
                }

                return $tags->pluck('name')->toArray();
            })
            ->toArray();
    }

    /**
     * Prepare the given value for storage (acts as a mutator).
     *
     * @param array<string, mixed> $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        // Return the value as-is, actual processing happens in the saving/saved observers
        return $value;
    }
}
