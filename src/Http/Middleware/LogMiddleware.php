<?php

namespace Unusualify\Modularous\Http\Middleware;

use Closure;
use Illuminate\Support\Str;
use Unusualify\Modularous\Facades\ModularousLog;

class LogMiddleware
{
    public function handle($request, Closure $next)
    {
        $requestId = (string) Str::uuid();

        ModularousLog::withContext([
            'request_id' => $requestId,
        ]);

        $response = $next($request);

        $response->headers->set('Request-Id', $requestId);

        return $response;
    }
}
