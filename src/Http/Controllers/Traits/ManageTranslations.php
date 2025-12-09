<?php

namespace Unusualify\Modularity\Http\Controllers\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

trait ManageTranslations
{
    /**
     * Get a translation from an array of keys.
     *
     * @param array $keys
     * @param array $parameters
     * @return string|null
     */
    public function getTranslationFromKeys($keys = [], $parameters = [])
    {
        $keyNotation = $this->findTranslationNotation($keys);

        if (!$keyNotation) {
            return null;
        }

        return Lang::get($keyNotation, $parameters);
    }

    /**
     * Find a translation notation from an array of keys.
     *
     * @param array $keys
     * @return string|null
     */
    public function findTranslationNotation($keys = [])
    {
        $keyNotation = Collection::make([
            ...$keys,
        ])->first(function ($key) {
            return Lang::has($key);
        });

        return $keyNotation;
    }

    /**
     * Get a module translation key.
     *
     * @return string
     */
    public function getModuleTranslationKey()
    {
        $moduleSnakeName = $this->module->getSnakeName();
        $snakeRouteName = Str::snake($this->routeName);

        return "modules.{$moduleSnakeName}.{$snakeRouteName}.name";
    }
}
