<?php

namespace Unusualify\Modularous\Facades;

use Illuminate\Support\Facades\Facade;
use Unusualify\Modularous\Support\Finder;

/**
 * @method static string|false getModel(string $table)
 * @method static string|false getRouteModel(string $routeName, bool $asClass = false)
 * @method static string|false getRepository(string $table)
 * @method static string|false getRouteRepository(string $routeName, bool $asClass = false)
 * @method static array getPossibleModels(string $routeName)
 * @method static array getClasses(string $path)
 * @method static \Illuminate\Support\Collection getAllModels()
 *
 * @see Finder
 */
class ModularousFinder extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Finder::class;
    }
}
