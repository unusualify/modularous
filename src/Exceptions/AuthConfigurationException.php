<?php

namespace Unusualify\Modularous\Exceptions;

use Exception;

class AuthConfigurationException extends Exception
{
    const GUARD_MISSING = 1;

    const PROVIDER_MISSING = 2;

    const PASSWORD_MISSING = 3;

    public static function guardMissing(): self
    {
        return new self(
            "Modularous auth guard configuration is missing. Please run 'php artisan modularous:update:laravel:configs' to update your auth configuration.",
            self::GUARD_MISSING
        );
    }

    public static function providerMissing(): self
    {
        return new self(
            "Modularous auth provider configuration is missing. Please run 'php artisan modularous:update:laravel:configs' to update your auth configuration.",
            self::PROVIDER_MISSING
        );
    }

    public static function passwordMissing(): self
    {
        return new self(
            "Modularous auth password configuration is missing. Please run 'php artisan modularous:update:laravel:configs' to update your auth configuration.",
            self::PASSWORD_MISSING
        );
    }
}
