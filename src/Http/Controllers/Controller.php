<?php

namespace Unusualify\Modularous\Http\Controllers;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as LaravelController;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Unusualify\Modularous\Exceptions\Handler as ModularousHandler;

class Controller extends LaravelController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct()
    {
        if (modularousConfig('bind_exception_handler', false)) {
            App::singleton(ExceptionHandler::class, ModularousHandler::class);
        }
    }

    /**
     * Attempts to unset the given middleware.
     *
     * @param string $middleware
     * @return void
     */
    public function removeMiddleware($middleware)
    {
        if (($key = array_search($middleware, Arr::pluck($this->middleware, 'middleware'))) !== false) {
            unset($this->middleware[$key]);
        }
    }
}
