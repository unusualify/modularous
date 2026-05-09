<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\Currency;

use Unusualify\Modularous\Contracts\CurrencyProviderInterface;

/**
 * Null implementation when no pricing module provides currency support.
 */
class NullCurrencyProvider implements CurrencyProviderInterface
{
    public function findByIso4217(string $isoCode): ?object
    {
        return null;
    }

    public function getCurrenciesForSelect(): array
    {
        return [];
    }

    public function isAvailable(): bool
    {
        return false;
    }
}
