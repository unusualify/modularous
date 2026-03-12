<?php

declare(strict_types=1);

namespace Unusualify\Modularity\Contracts;

use Illuminate\Support\Collection;

/**
 * Contract for resolving currency data. Implementations may be provided by
 * pricing modules (e.g. SystemPricing) when enabled. When no pricing module
 * is present, NullCurrencyProvider is used.
 *
 * @see \Unusualify\Modularity\Services\Currency\NullCurrencyProvider
 */
interface CurrencyProviderInterface
{
    /**
     * Find a currency by ISO 4217 code.
     */
    public function findByIso4217(string $isoCode): ?object;

    /**
     * Get currencies for select inputs (id, name, iso).
     *
     * @return array<int, array{id: int, name: string, iso: string}>
     */
    public function getCurrenciesForSelect(): array;

    /**
     * Whether a currency provider is available (e.g. SystemPricing module is enabled).
     */
    public function isAvailable(): bool;
}
