<?php

namespace Unusualify\Modularity\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Unusualify\Modularity\Services\Security\SecurityService;

class RequireMfaMiddleware
{
    public function __construct(
        protected SecurityService $securityService,
    ) {}

    public function handle(Request $request, Closure $next)
    {
        if (! modularityConfig('security.enabled', false)) {
            return $next($request);
        }

        $user = $request->user();

        if (! $this->securityService->userRequiresMfa($user)) {
            return $next($request);
        }

        if ($this->securityService->userHasEnabledMfa($user)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'MFA is required for this role.',
            ], 403);
        }

        if (Route::has('admin.login.form')) {
            auth()->logout();

            return redirect()->route('admin.login.form')->withErrors([
                'mfa' => 'MFA setup is required before continuing.',
            ]);
        }

        abort(403, 'MFA is required for this role.');
    }
}
