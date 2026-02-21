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
        return $this->viewFactory->make(modularityBaseKey() . '::auth.register', $this->buildAuthViewData('pre_register'));
    }
}
