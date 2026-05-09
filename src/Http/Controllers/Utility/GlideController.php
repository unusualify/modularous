<?php

namespace Unusualify\Modularous\Http\Controllers;

use Illuminate\Foundation\Application;
use Unusualify\Modularous\Services\MediaLibrary\Glide;

class GlideController
{
    public function __invoke($path, Application $app)
    {
        return $app->make(Glide::class)->render($path);
    }
}
