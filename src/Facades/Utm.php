<?php

namespace Unusualify\Modularity\Facades;

use Illuminate\Support\Facades\Facade;

class Utm extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'modularity.utm';
    }
}
