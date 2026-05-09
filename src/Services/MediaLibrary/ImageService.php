<?php

namespace Unusualify\Modularous\Services\MediaLibrary;

use Illuminate\Support\Facades\Facade;

class ImageService extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'imageService';
    }
}
