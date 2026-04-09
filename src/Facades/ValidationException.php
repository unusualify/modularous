<?php

declare(strict_types=1);

namespace Unusualify\Modularity\Facades;

use Illuminate\Support\Facades\Facade;
use Unusualify\Modularity\Services\ValidationExceptionFactory;

/**
 * @method static \Unusualify\Modularity\Exceptions\ValidationException withMessages(array $messages)
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
