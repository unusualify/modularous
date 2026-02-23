<?php

declare(strict_types=1);

namespace Unusualify\Modularity\Http\Controllers\Auth;

use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Services\MessageStage;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    public function broker()
    {
        return Password::broker(Modularity::getAuthProviderName());
    }

    public function showLinkRequestForm()
    {
        return $this->viewFactory->make(modularityBaseKey() . '::auth.passwords.email', $this->buildAuthViewData('forgot_password'));
    }

    protected function sendResetLinkResponse(Request $request, $response): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        return $request->wantsJson()
            ? new JsonResponse([
                'message' => ___($response),
                'variant' => MessageStage::SUCCESS,
            ], 200)
            : back()->with('status', ___($response));
    }

    protected function sendResetLinkFailedResponse(Request $request, $response): JsonResponse|\Illuminate\Http\RedirectResponse
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
