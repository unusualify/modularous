<?php

namespace Modules\Cms\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Cms\Contracts\CanonicalUrlResolverInterface;

class CanonicalLocaleMiddleware
{
    public function __construct(
        protected CanonicalUrlResolverInterface $canonicalUrlResolver,
    ) {}

    public function handle(Request $request, Closure $next)
    {
        if (! modularousConfig('cms_routing.redirect_to_canonical', false)) {
            return $next($request);
        }

        $resolved = $this->canonicalUrlResolver->resolve(
            host: $request->getHost(),
            path: $request->getPathInfo(),
            locale: app()->getLocale(),
        );

        if (($resolved['should_redirect'] ?? false) === true) {
            return redirect()->to($resolved['redirect_to'], 301);
        }

        return $next($request);
    }
}
