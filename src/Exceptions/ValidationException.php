<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Exceptions;

use Illuminate\Validation\ValidationException as BaseValidationException;

class ValidationException extends BaseValidationException
{
    public function variant(string $variant = 'error'): static
    {
        $errors = $this->errors();
        $message = $this->summarizeErrors($errors);

        $this->response = response()->json([
            'message' => $message,
            'errors' => $errors,
            'variant' => $variant,
        ], 422);

        return $this;
    }

    protected function summarizeErrors(array $errors): string
    {
        foreach ($errors as $messages) {
            if (is_array($messages) && isset($messages[0]) && is_string($messages[0]) && $messages[0] !== '') {
                return $messages[0];
            }
        }

        return __('The given data was invalid.');
    }
}
