<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;
use Unusualify\Modularous\Services\RedirectService;

final class RedirectorMiddleware
{
    public function __construct(private readonly RedirectService $redirectService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $redirectUrl = $this->redirectService->pull();
        if ($redirectUrl) {
            return Redirect::to($redirectUrl);
        }

        return $next($request);
    }
}
