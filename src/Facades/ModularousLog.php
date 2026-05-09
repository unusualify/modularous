<?php

namespace Unusualify\Modularous\Facades;

use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\Facade;

/**
 * @see LogManager
 */
class ModularousLog extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'modularous.log';
    }
}
