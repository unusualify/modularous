<?php

namespace Unusualify\Modularous\Facades;

use Illuminate\Support\Facades\Facade;

class Navigation extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'modularous.navigation';
    }
}
