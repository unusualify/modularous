<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Http\Controllers\Auth;

use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Services\MessageStage;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    public function broker()
    {
        return Password::broker(Modularous::getAuthProviderName());
    }

    public function showLinkRequestForm()
    {
        return $this->viewFactory->make(modularousBaseKey() . '::auth.passwords.email', $this->buildAuthViewData('forgot_password'));
    }

    protected function sendResetLinkResponse(Request $request, $response): JsonResponse|RedirectResponse
    {
        return $request->wantsJson()
            ? new JsonResponse([
                'message' => ___($response),
                'variant' => MessageStage::SUCCESS,
            ], 200)
            : back()->with('status', ___($response));
    }

    protected function sendResetLinkFailedResponse(Request $request, $response): JsonResponse|RedirectResponse
    {
        return $request->wantsJson()
            ? new JsonResponse([
                'email' => [___($response)],
                'message' => ___($response),
                'variant' => MessageStage::WARNING,
            ])
            : back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => ___($response)]);
    }
}
