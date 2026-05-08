<?php

namespace Unusualify\Modularity\Http\Controllers\Traits\Utilities;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Unusualify\Modularity\Services\MessageStage;
use Unusualify\Modularity\Services\Security\SecurityService;

trait EnforcesMfaSetupOnLogin
{
    protected function enforceMfaSetupOnLogin(Request $request, $user): JsonResponse|RedirectResponse|null
    {
        /** @var SecurityService|null $securityService */
        $securityService = app()->bound(SecurityService::class)
            ? app()->make(SecurityService::class)
            : null;

        if (
            $securityService === null
            || ! modularityConfig('security.enabled', false)
            || ! modularityConfig('security.mfa.enabled', false)
            || ! modularityConfig('security.mfa.strict', true)
            || ! $securityService->userRequiresMfa($user)
            || $securityService->userHasEnabledMfa($user)
        ) {
            return null;
        }

        $this->guard()->logout();

        $message = __('MFA setup is required for your role.');

        return $request->wantsJson()
            ? new JsonResponse([
                'message' => $message,
                'variant' => MessageStage::WARNING,
            ], 403)
            : redirect()->to(route(Route::hasAdmin('login.form')))->withErrors([
                'mfa' => $message,
            ]);
    }
}
