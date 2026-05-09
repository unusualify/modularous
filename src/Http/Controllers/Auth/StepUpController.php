<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Unusualify\Modularous\Services\Security\StepUpService;

class StepUpController extends Controller
{
    public function __construct(
        protected StepUpService $stepUpService,
    ) {
        parent::__construct();
    }

    protected function guestMiddlewareExcept(): array
    {
        return ['showForm', 'verify', 'resend'];
    }

    public function showForm()
    {
        if (! $this->stepUpService->hasActiveChallenge(request())) {
            return redirect()->route(Route::hasAdmin('dashboard'));
        }

        return $this->viewFactory->make(
            modularousBaseKey() . '::auth.login',
            $this->buildAuthViewData($this->stepUpService->pageKey(), [
                'formAttributes' => [
                    'subtitle' => __('We sent a verification code to your email.'),
                ],
            ])
        );
    }

    public function verify(Request $request)
    {
        return $this->stepUpService->verify($request);
    }

    public function resend(Request $request)
    {
        return $this->stepUpService->resend($request);
    }
}
