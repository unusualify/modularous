<?php

namespace Unusualify\Modularous\Tests\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Unusualify\Modularous\Http\Controllers\Traits\Utilities\SendsEmailVerificationRegister;

class ControllerUsingSendsEmailVerificationRegister extends Controller
{
    use AuthorizesRequests, ValidatesRequests, SendsEmailVerificationRegister;
}
