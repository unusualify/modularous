<?php

namespace Unusualify\Modularous\Facades;

use Illuminate\Support\Facades\Facade;

class ModularousRoutes extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Unusualify\Modularous\Support\ModularousRoutes::class;
    }
}
