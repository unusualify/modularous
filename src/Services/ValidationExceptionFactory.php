<?php

declare(strict_types=1);

namespace Unusualify\Modularity\Services;

use Unusualify\Modularity\Exceptions\ValidationException;

class ValidationExceptionFactory
{
    public function withMessages(array $messages): ValidationException
    {
        /** @var ValidationException $exception */
        $exception = ValidationException::withMessages($messages);

        return $exception->variant();
    }
}
