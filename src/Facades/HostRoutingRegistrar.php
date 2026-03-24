<?php

namespace Unusualify\Modularity\Facades;

use Illuminate\Support\Facades\Facade;
use Unusualify\Modularity\Suppo\HostRouting;

/**
 * @method static void registerRoutes()
 * @method static void addRoute(string $host, string $action)
 * @method static void removeRoute(string $host)
 * @method static array getRegisteredRoutes()
 * @method static void clearRegisteredRoutes()
 *
 * @see HostRouting
 */
class HostRoutingRegistrar extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'unusualify.hostRouting';
    }
}
