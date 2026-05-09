<?php

namespace Unusualify\Modularous\Facades;

use Illuminate\Support\Facades\Facade;

class Redirect extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'modularous.redirect';
    }
}
