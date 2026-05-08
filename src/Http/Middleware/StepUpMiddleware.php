<?php

namespace Unusualify\Modularity\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Unusualify\Modularity\Services\Security\SecurityService;
use Unusualify\Modularity\Services\Security\StepUpService;

class StepUpMiddleware
{
    public function __construct(
        protected SecurityService $securityService,
        protected StepUpService $stepUpService,
    ) {}

    public function handle(Request $request, Closure $next, ?string $capability = null)
    {
        if (! modularityConfig('security.step_up.enabled', false)) {
            return $next($request);
        }

        $user = $request->user();
        $currentRouteName = $request->route()?->getName();

        if (! $user || ! is_string($currentRouteName) || $currentRouteName === '') {
            return $next($request);
        }

        $matchedCapability = $this->securityService->matchedUserStepUpCapability($user, $currentRouteName, $capability);

        if (! $matchedCapability) {
            return $next($request);
        }

        $verifiedAt = (int) $request->session()->get('security_step_up_verified_at', 0);
        $ttlMinutes = (int) modularityConfig('security.session.step_up_ttl_minutes', 15);

        if ($verifiedAt > 0 && (time() - $verifiedAt) <= ($ttlMinutes * 60)) {
            return $next($request);
        }

        return $this->stepUpService->interrupt($request, $matchedCapability);
    }
}
