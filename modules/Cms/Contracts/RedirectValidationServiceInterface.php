<?php

namespace Modules\Cms\Contracts;

interface RedirectValidationServiceInterface
{
    public function validate(string $fromPath, string $toPath, array $options = []): array;
}
