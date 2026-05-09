<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services;

use Unusualify\Modularous\Exceptions\ValidationException;

class ValidationExceptionFactory
{
    public function withMessages(array $messages): ValidationException
    {
        /** @var ValidationException $exception */
        $exception = ValidationException::withMessages($messages);

        return $exception->variant();
    }
}
