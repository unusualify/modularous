<?php

namespace Unusualify\Modularity\Facades;

use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\Facade;

/**
 * @see LogManager
 */
class ModularityLog extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'modularity.log';
    }
}
