<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Http\Controllers\Auth;

use Illuminate\Foundation\Application;
use Unusualify\Modularous\Facades\Register;
use Unusualify\Modularous\Http\Controllers\Traits\Utilities\SendsEmailVerificationRegister;

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
        return $this->viewFactory->make(modularousBaseKey() . '::auth.register', $this->buildAuthViewData('pre_register'));
    }
}
