<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Facades;

use Illuminate\Support\Facades\Facade;
use Unusualify\Modularous\Services\ValidationExceptionFactory;

/**
 * @method static \Unusualify\Modularous\Exceptions\ValidationException withMessages(array $messages)
 *
 * @see ValidationExceptionFactory
 */
class ValidationException extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ValidationExceptionFactory::class;
    }
}
