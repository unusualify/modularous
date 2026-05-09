<?php

namespace Unusualify\Modularous\Facades;

use Illuminate\Support\Facades\Password;
use Unusualify\Modularous\Brokers\RegisterBroker;
use Unusualify\Modularous\Brokers\RegisterBrokerManager;

/**
 * @method static \Illuminate\Contracts\Auth\PasswordBroker broker(string|null $name = null)
 * @method static string getDefaultDriver()
 * @method static void setDefaultDriver(string $name)
 * @method static string sendResetLink(array $credentials, \Closure|null $callback = null)
 * @method static mixed reset(array $credentials, \Closure $callback)
 * @method static \Illuminate\Contracts\Auth\CanResetPassword|null getUser(array $credentials)
 * @method static string createToken(\Illuminate\Contracts\Auth\CanResetPassword $user)
 * @method static void deleteToken(\Illuminate\Contracts\Auth\CanResetPassword $user)
 * @method static bool tokenExists(\Illuminate\Contracts\Auth\CanResetPassword $user, string $token)
 * @method static \Illuminate\Auth\Passwords\TokenRepositoryInterface getRepository()
 *
 * @see RegisterBrokerManager
 * @see RegisterBroker
 */
class Register extends Password
{
    /**
     * Constant representing a successfully registered user.
     *
     * @var string
     */
    const VERIFIED_EMAIL_REGISTER = RegisterBroker::VERIFICATION_SUCCESS;

    const VERIFICATION_LINK_SENT = RegisterBroker::VERIFICATION_LINK_SENT;

    const ALREADY_REGISTERED = RegisterBroker::ALREADY_REGISTERED;

    const INVALID_VERIFICATION_TOKEN = RegisterBroker::INVALID_VERIFICATION_TOKEN;

    const VERIFICATION_THROTTLED = RegisterBroker::VERIFICATION_THROTTLED;

    protected static function getFacadeAccessor()
    {
        return 'auth.register';
    }
}
