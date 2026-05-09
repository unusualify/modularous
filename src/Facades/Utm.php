<?php

namespace Unusualify\Modularous\Facades;

use Illuminate\Support\Facades\Facade;

class Utm extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'modularous.utm';
    }
}
