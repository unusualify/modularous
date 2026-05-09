<?php

namespace Unusualify\Modularous\Http\Controllers;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Unusualify\Modularous\Http\Controllers\Traits\ManageForm;

class VerificationController extends Controller
{
    use ManageForm;

    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();

        return view(modularousBaseKey() . '::auth.success', [
            'pageTitle' => __('authentication.verification-complete'),
            'taskState' => [
                'status' => 'success',
                'title' => __('authentication.verification-complete'),
                'description' => __('authentication.verification-complete-description'),
                'button_text' => __('Back'),
                'button_url' => route('admin.dashboard'),
            ],
        ]);
    }

    public function send(Request $request)
    {
        // return back()->with('message', 'Verification link sent!');
        $request->user()->sendEmailVerificationNotification();

        return back()->with('message', 'Verification link sent!');
    }
}
