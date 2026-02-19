<?php

declare(strict_types=1);

namespace Unusualify\Modularity\Http\Controllers\Auth;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Unusualify\Modularity\Facades\Register;
use Unusualify\Modularity\Http\Controllers\Traits\Utilities\SendsEmailVerificationRegister;

class PreRegisterController extends Controller
{
    use SendsEmailVerificationRegister;

    public function __construct(?Application $app = null)
    {
        parent::__construct();
    }

    public function broker()
    {
        return Register::broker();
    }

    public function showEmailForm()
    {
        return $this->viewFactory->make(modularityBaseKey() . '::auth.register', [
            'attributes' => $this->authBannerAttributes(),
            'formAttributes' => array_merge(
                ['title' => $this->authFormTitle(__('authentication.create-an-account'), ['transform' => ''])],
                $this->authFormBaseAttributes(
                    'pre_register_form',
                    route(Route::hasAdmin('register.verification')),
                    'authentication.register'
                )
            ),
            'formSlots' => $this->haveAccountOptionSlot(),
            'slots' => [
                'bottom' => $this->authBottomSlots([
                    $this->oauthGoogleButtonSlot('sign-up'),
                ]),
            ],
        ]);
    }
}
