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
        return $this->viewFactory->make(modularityBaseKey() . '::auth.passwords.email', [
            'attributes' => ['noSecondSection' => true],
            'formAttributes' => array_merge(
                ['title' => $this->authFormTitle(__('authentication.forgot-password'))],
                $this->authFormBaseAttributes(
                    'forgot_password_form',
                    route(Route::hasAdmin('password.reset.email')),
                    'authentication.reset-send',
                    ['hasSubmit' => false]
                )
            ),
            'formSlots' => [
                'bottom' => [
                    'tag' => 'v-sheet',
                    'attributes' => [
                        'class' => 'd-flex pb-5 justify-space-between w-100 text-black my-5',
                    ],
                    'elements' => [
                        [
                            'tag' => 'v-btn',
                            'elements' => __('authentication.sign-in'),
                            'attributes' => [
                                'variant' => 'elevated',
                                'href' => route(Route::hasAdmin('login.form')),
                                'class' => '',
                                'color' => 'success',
                                'density' => 'default',
                            ],
                        ],
                        [
                            'tag' => 'v-btn',
                            'elements' => __('authentication.reset-password'),
                            'attributes' => [
                                'variant' => 'elevated',
                                'href' => '',
                                'class' => '',
                                'type' => 'submit',
                                'density' => 'default',
                            ],
                        ],
                    ],
                ],
            ],
            'slots' => [
                'bottom' => $this->authBottomSlots([
                    $this->oauthGoogleButtonSlot('sign-in'),
                    $this->createAccountButtonSlot(),
                ]),
            ],
        ]);
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
