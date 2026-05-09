<?php

namespace Unusualify\Modularous\Exceptions;

use Exception;

class ModularousSystemPathException extends Exception
{
    public function __construct($message = 'You cannot set system modules path in production', $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
