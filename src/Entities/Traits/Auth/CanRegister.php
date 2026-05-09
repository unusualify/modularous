<?php

namespace Unusualify\Modularous\Entities\Traits\Auth;

use Illuminate\Support\Facades\App;
use Unusualify\Modularous\Notifications\EmailVerification;

trait CanRegister
{
    /**
     * Get the e-mail address where password reset links are sent.
     *
     * @return string
     */
    public function getEmailForRegister()
    {
        return $this->email;
    }

    /**
     * Send the password reset notification.
     *
     * @param string $token
     * @return void
     */
    public function sendRegisterNotification($token, $parameters = [])
    {
        $emailVerificationClass = config('modularous.verification_email_class', EmailVerification::class);
        $this->notify(App::makeWith($emailVerificationClass, ['token' => $token, 'parameters' => $parameters]));
    }
}
